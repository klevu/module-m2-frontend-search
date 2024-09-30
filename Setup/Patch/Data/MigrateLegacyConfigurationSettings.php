<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Setup\Patch\Data;

use Klevu\FrontendSearch\Constants;
use Klevu\FrontendSearch\Model\Config\Source\ThemeOptionSource;
use Klevu\FrontendSearch\Service\Provider\ThemeProvider;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateLegacyConfigurationSettings implements DataPatchInterface
{
    public const XML_PATH_LEGACY_QUICK_ENABLED = 'klevu_search/general/enabled';
    public const XML_PATH_LEGACY_SRLP_THEME = 'klevu_search/searchlanding/landenabled';
    public const XML_PATH_LEGACY_SEARCH_BOX_SELECTOR = 'klevu_search/developer/quicksearch_selector';
    public const XML_VALUE_LEGACY_SRLP_THEME_NATIVE = '0';
    public const XML_VALUE_LEGACY_SRLP_THEME_PL = '1';
    public const XML_VALUE_LEGACY_SRLP_THEME_JS = '2';

    /**
     * @var WriterInterface
     */
    private readonly WriterInterface $configWriter;
    /**
     * @var ConfigCollectionFactory
     */
    private readonly ConfigCollectionFactory $configCollectionFactory;
    /**
     * @var mixed[]|null
     */
    private ?array $legacyConfigSettings = null;

    /**
     * @param WriterInterface $configWriter
     * @param ConfigCollectionFactory $configCollectionFactory
     */
    public function __construct(
        WriterInterface $configWriter,
        ConfigCollectionFactory $configCollectionFactory,
    ) {
        $this->configWriter = $configWriter;
        $this->configCollectionFactory = $configCollectionFactory;
    }

    /**
     * @return $this
     */
    public function apply(): self
    {
        $this->migrateQuickSearch();
        $this->migrateSrlp();
        $this->migrateSearchBoxSelector();

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return string[]
     */

    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return void
     */
    private function migrateQuickSearch(): void
    {
        $this->renameConfigValue(
            fromPath: static::XML_PATH_LEGACY_QUICK_ENABLED,
            toPath: Constants::XML_PATH_QUICK_ENABLED,
        );
    }

    /**
     * @return void
     */
    private function migrateSrlp(): void
    {
        // mapValues sets PL to native (in case PL module is not installed)
        $this->renameConfigValue(
            fromPath: static::XML_PATH_LEGACY_SRLP_THEME,
            toPath: ThemeProvider::XML_PATH_SEARCH_THEME,
            mapValues: [
                static::XML_VALUE_LEGACY_SRLP_THEME_NATIVE => (string)ThemeOptionSource::SRLP_THEME_VALUE_DISABLED,
                static::XML_VALUE_LEGACY_SRLP_THEME_PL => (string)ThemeOptionSource::SRLP_THEME_VALUE_DISABLED,
                static::XML_VALUE_LEGACY_SRLP_THEME_JS => (string)ThemeOptionSource::SRLP_THEME_VALUE_KLEVU,
            ],
        );
    }

    private function migrateSearchBoxSelector(): void
    {
        $this->renameConfigValue(
            fromPath: static::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
            toPath: Constants::XML_PATH_SEARCH_BOX_SELECTOR,
        );
    }

    /**
     * @param string $fromPath
     * @param string $toPath
     * @param mixed[]|null $mapValues
     *
     * @return void
     */
    private function renameConfigValue(
        string $fromPath,
        string $toPath,
        ?array $mapValues = [],
    ): void {
        $legacyConfigSettings = $this->getLegacyConfigSettings();
        if (!($legacyConfigSettings[$fromPath] ?? null)) {
            return;
        }

        foreach ($legacyConfigSettings[$fromPath] as $scope => $scopeValues) {
            foreach ($scopeValues as $scopeId => $value) {
                $this->configWriter->save(
                    path: $toPath,
                    value: $mapValues[$value] ?? $value,
                    scope: $scope,
                    scopeId: $scopeId,
                );
            }
        }
    }

    /**
     * @return mixed[]
     */
    private function getLegacyConfigSettings(): array
    {
        if (null === $this->legacyConfigSettings) {
            $configCollection = $this->configCollectionFactory->create();
            $configCollection->addFieldToFilter(
                field: 'path',
                condition: [
                    'in' => [
                        static::XML_PATH_LEGACY_QUICK_ENABLED,
                        static::XML_PATH_LEGACY_SRLP_THEME,
                        static::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
                    ],
                ],
            );
            $this->legacyConfigSettings = [];
            /** @var Value[] $result */
            $result = $configCollection->getItems();
            foreach ($result as $row) {
                $this->legacyConfigSettings[$row->getPath()][$row->getScope()][$row->getScopeId()] = $row->getValue();
            }
        }

        return $this->legacyConfigSettings;
    }
}
