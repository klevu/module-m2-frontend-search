<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\Setup\Patch\Data;

use Klevu\FrontendSearch\Constants;
use Klevu\FrontendSearch\Service\Provider\ThemeProvider;
use Klevu\FrontendSearch\Setup\Patch\Data\MigrateLegacyConfigurationSettings;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Website\WebsiteFixturesPool;
use Klevu\TestFixtures\Website\WebsiteTrait;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\Writer as ConfigWriter;
use Magento\Framework\App\MutableScopeConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klevu\FrontendSearch\Setup\Patch\Data\MigrateLegacyConfigurationSettings
 */
class MigrateLegacyConfigurationSettingsTest extends TestCase
{
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;
    use WebsiteTrait;
    use StoreTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;
    /**
     * @var ScopeConfigInterface|null
     */
    private ?ScopeConfigInterface $scopeConfig = null;
    /**
     * @var ConfigResource|null
     */
    private ?ConfigResource $configResource = null;
    /**
     * @var ConfigWriter|null
     */
    private ?ConfigWriter $configWriter = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();

        $this->implementationFqcn = MigrateLegacyConfigurationSettings::class;
        $this->interfaceFqcn = DataPatchInterface::class;

        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->configResource = $this->objectManager->get(ConfigResource::class);
        $this->configWriter = $this->objectManager->get(ConfigWriter::class);

        $this->websiteFixturesPool = $this->objectManager->get(WebsiteFixturesPool::class);
        $this->storeFixturesPool = $this->objectManager->get(StoreFixturesPool::class);

        $this->createStore([
            'key' => 'test_store_1',
        ]);
        $this->createWebsite();
        $testWebsite = $this->websiteFixturesPool->get('test_website');
        $this->createStore([
            'key' => 'test_store_2',
            'code' => 'klevu_test_store_2',
            'website_id' => $testWebsite->getId(),
        ]);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->storeFixturesPool->rollback();
        $this->websiteFixturesPool->rollback();
    }

    public function testGetDependencies(): void
    {
        $dependencies = MigrateLegacyConfigurationSettings::getDependencies();

        $this->assertSame([], $dependencies);
    }

    public function testGetAliases(): void
    {
        $migrateLegacyConfigurationSettingsPatch = $this->instantiateTestObject();
        $aliases = $migrateLegacyConfigurationSettingsPatch->getAliases();

        $this->assertSame([], $aliases);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_QuickSearchEnabled_EnabledGlobal(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
            value: '1',
            scope: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            scopeId: 0,
        );
        $this->scopeConfig->clean();

        $this->assertTrue(
            $this->scopeConfig->isSetFlag(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertTrue(
            $this->scopeConfig->isSetFlag(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertTrue(
            $this->scopeConfig->isSetFlag(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertTrue(
            $this->scopeConfig->isSetFlag(
                Constants::XML_PATH_QUICK_ENABLED,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertTrue(
            $this->scopeConfig->isSetFlag(
                Constants::XML_PATH_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertTrue(
            $this->scopeConfig->isSetFlag(
                Constants::XML_PATH_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_QuickSearchEnabled_DisabledGlobal(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
            value: '0',
            scope: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            scopeId: 0,
        );
        $this->scopeConfig->clean();

        $this->assertFalse(
            $this->scopeConfig->isSetFlag(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertFalse(
            $this->scopeConfig->isSetFlag(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertFalse(
            $this->scopeConfig->isSetFlag(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertFalse(
            $this->scopeConfig->isSetFlag(
                Constants::XML_PATH_QUICK_ENABLED,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertFalse(
            $this->scopeConfig->isSetFlag(
                Constants::XML_PATH_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertFalse(
            $this->scopeConfig->isSetFlag(
                Constants::XML_PATH_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_QuickSearchEnabled_WebsiteScope(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
            value: '1',
            scope: ScopeInterface::SCOPE_WEBSITES,
            scopeId: $testStore1->getWebsiteId(),
        );
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
            value: '0',
            scope: ScopeInterface::SCOPE_WEBSITES,
            scopeId: $testStore2->getWebsiteId(),
        );
        $this->scopeConfig->clean();

        $this->assertTrue(
            $this->scopeConfig->isSetFlag(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore1->getWebsiteId(),
            ),
        );
        $this->assertFalse(
            $this->scopeConfig->isSetFlag(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore2->getWebsiteId(),
            ),
        );
        $this->assertTrue(
            $this->scopeConfig->isSetFlag(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertFalse(
            $this->scopeConfig->isSetFlag(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertTrue(
            $this->scopeConfig->isSetFlag(
                Constants::XML_PATH_QUICK_ENABLED,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore1->getWebsiteId(),
            ),
        );
        $this->assertFalse(
            $this->scopeConfig->isSetFlag(
                Constants::XML_PATH_QUICK_ENABLED,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore2->getWebsiteId(),
            ),
        );
        $this->assertTrue(
            $this->scopeConfig->isSetFlag(
                Constants::XML_PATH_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertFalse(
            $this->scopeConfig->isSetFlag(
                Constants::XML_PATH_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_QuickSearchEnabled_StoreScope(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
            value: '1',
            scope: ScopeInterface::SCOPE_STORES,
            scopeId: $testStore1->getId(),
        );
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
            value: '0',
            scope: ScopeInterface::SCOPE_STORES,
            scopeId: $testStore2->getId(),
        );
        $this->scopeConfig->clean();

        $this->assertTrue(
            $this->scopeConfig->isSetFlag(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertFalse(
            $this->scopeConfig->isSetFlag(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertTrue(
            $this->scopeConfig->isSetFlag(
                Constants::XML_PATH_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertFalse(
            $this->scopeConfig->isSetFlag(
                Constants::XML_PATH_QUICK_ENABLED,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SrlpTheme_NativeTheme_Global(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
            value: '0',
            scope: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            scopeId: 0,
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SrlpTheme_KlevuTheme_Global(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
            value: '2',
            scope: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            scopeId: 0,
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: '2',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: '2',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '2',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SrlpTheme_PLTheme_Global(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
            value: '1',// PL
            scope: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            scopeId: 0,
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: '1',// PL
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: '1',// PL
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '1',// PL
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: '0',// PL converted to Native. PL modules handles PL migration
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: '0',// PL converted to Native. PL modules handles PL migration
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0',// PL converted to Native. PL modules handles PL migration
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SrlpTheme_WebsiteScope(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
            value: '2',
            scope: ScopeInterface::SCOPE_WEBSITES,
            scopeId: $testStore1->getWebsiteId(),
        );
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
            value: '0',
            scope: ScopeInterface::SCOPE_WEBSITES,
            scopeId: $testStore2->getWebsiteId(),
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: '2',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore1->getWebsiteId(),
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore2->getWebsiteId(),
            ),
        );
        $this->assertSame(
            expected: '2',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore1->getWebsiteId(),
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore2->getWebsiteId(),
            ),
        );
        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SrlpTheme_StoreScope(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
            value: '2',
            scope: ScopeInterface::SCOPE_STORES,
            scopeId: $testStore1->getId(),
        );
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
            value: '1', // PL
            scope: ScopeInterface::SCOPE_STORES,
            scopeId: $testStore2->getId(),
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: '2',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '1', // PL
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0', // PL converted to Native. PL modules handles PL migration
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_Disabled_StoreScope(): void
    {
        $this->deleteExistingKlevuConfig();

        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
            value: '2',
            scope: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            scopeId: 0,
        );
        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
            value: '0',
            scope: ScopeInterface::SCOPE_STORES,
            scopeId: $testStore1->getId(),
        );
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
            value: '0',
            scope: ScopeInterface::SCOPE_STORES,
            scopeId: $testStore2->getId(),
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SRLP_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_SEARCH_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SearchSelector_Global(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
            value: 'input[type=search][name=search]',
            scope: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            scopeId: 0,
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: 'input[type=search][name=search]',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: 'input[type=search][name=search]',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: 'input[type=search][name=search]',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: 'input[type=search][name=search]',
            actual: $this->scopeConfig->getValue(
                Constants::XML_PATH_SEARCH_BOX_SELECTOR,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: 'input[type=search][name=search]',
            actual: $this->scopeConfig->getValue(
                Constants::XML_PATH_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: 'input[type=search][name=search]',
            actual: $this->scopeConfig->getValue(
                Constants::XML_PATH_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SearchSelector_WebsiteScope(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
            value: 'input[type=search][name=search]',
            scope: ScopeInterface::SCOPE_WEBSITES,
            scopeId: $testStore1->getWebsiteId(),
        );
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
            value: 'input[type=text][name=qry]',
            scope: ScopeInterface::SCOPE_WEBSITES,
            scopeId: $testStore2->getWebsiteId(),
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: 'input[type=search][name=search]',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore1->getWebsiteId(),
            ),
        );
        $this->assertSame(
            expected: 'input[type=text][name=qry]',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore2->getWebsiteId(),
            ),
        );
        $this->assertSame(
            expected: 'input[type=search][name=search]',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: 'input[type=text][name=qry]',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: 'input[type=search][name=search]',
            actual: $this->scopeConfig->getValue(
                Constants::XML_PATH_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore1->getWebsiteId(),
            ),
        );
        $this->assertSame(
            expected: 'input[type=text][name=qry]',
            actual: $this->scopeConfig->getValue(
                Constants::XML_PATH_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore2->getWebsiteId(),
            ),
        );
        $this->assertSame(
            expected: 'input[type=search][name=search]',
            actual: $this->scopeConfig->getValue(
                Constants::XML_PATH_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: 'input[type=text][name=qry]',
            actual: $this->scopeConfig->getValue(
                Constants::XML_PATH_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SearchSelector_StoreScope(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
            value: 'input[type=search][name=search]',
            scope: ScopeInterface::SCOPE_STORES,
            scopeId: $testStore1->getId(),
        );
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
            value: 'input[type=text][name=qry]',
            scope: ScopeInterface::SCOPE_STORES,
            scopeId: $testStore2->getId(),
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: 'input[type=search][name=search]',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: 'input[type=text][name=qry]',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: 'input[type=search][name=search]',
            actual: $this->scopeConfig->getValue(
                Constants::XML_PATH_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: 'input[type=text][name=qry]',
            actual: $this->scopeConfig->getValue(
                Constants::XML_PATH_SEARCH_BOX_SELECTOR,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function deleteExistingKlevuConfig(): void
    {
        $connection = $this->configResource->getConnection();
        $connection->delete(
            $this->configResource->getMainTable(),
            [
                'path like "klevu%"',
            ],
        );

        $this->cleanScopeConfig();
    }

    /**
     * @return void
     */
    private function cleanScopeConfig(): void
    {
        /** @var MutableScopeConfig $scopeConfig */
        $scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $scopeConfig->clean();
    }
}
