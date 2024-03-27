<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Service\Provider;

use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Klevu\FrontendSearch\Model\Config\Source\ThemeOptionSource;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ThemeProvider implements ThemeProviderInterface
{
    public const XML_PATH_SEARCH_THEME = 'klevu_frontend/srlp/theme';

    /**
     * @var ScopeProviderInterface
     */
    private readonly ScopeProviderInterface $scopeProvider;
    /**
     * @var ScopeConfigInterface
     */
    private readonly ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeProviderInterface $scopeProvider
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeProviderInterface $scopeProvider,
        ScopeConfigInterface $scopeConfig,
    ) {
        $this->scopeProvider = $scopeProvider;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return int
     */
    public function get(): int
    {
        $scope = $this->scopeProvider->getCurrentScope();

        return (int)($this->scopeConfig->getValue(
            static::XML_PATH_SEARCH_THEME,
            $scope->getScopeType(),
            $scope->getScopeId(),
        ));
    }

    /**
     * @return bool
     */
    public function isKlevuTheme(): bool
    {
        return $this->get() === ThemeOptionSource::SRLP_THEME_VALUE_KLEVU;
    }

    /**
     * @return bool
     */
    public function isNativeTheme(): bool
    {
        return !$this->isKlevuTheme();
    }
}
