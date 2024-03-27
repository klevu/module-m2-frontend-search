<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Observer;

use Klevu\Configuration\Service\IsStoreIntegratedServiceInterface;
use Klevu\Frontend\Exception\OutputDisabledException;
use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Klevu\FrontendSearch\Service\Provider\ThemeProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

class UpdateSearchResultsPageLayout implements ObserverInterface
{
    public const PARAM_KLEVU_THEME = 'klevu';
    public const REQUEST_PARAM_KLEVU_SRLP_LAYOUT_PREVIEW = 'klevu_layout_preview';
    private const LAYOUT_HANDLE_SEARCH_RESULTS_KLEVU = 'klevu_search_results';
    private const LAYOUT_HANDLE_SEARCH_RESULTS_MAGENTO = 'catalogsearch_result_index';

    /**
     * @var RequestInterface
     */
    private readonly RequestInterface $request;
    /**
     * @var ThemeProviderInterface
     */
    private readonly ThemeProviderInterface $themeProvider;
    /**
     * @var SettingsProviderInterface
     */
    private readonly SettingsProviderInterface $landingUrlProvider;
    /**
     * @var IsStoreIntegratedServiceInterface
     */
    private readonly IsStoreIntegratedServiceInterface $isStoreIntegratedService;

    /**
     * @param RequestInterface $request
     * @param ThemeProviderInterface $themeProvider
     * @param SettingsProviderInterface $landingUrlProvider
     * @param IsStoreIntegratedServiceInterface $isStoreIntegratedService
     */
    public function __construct(
        RequestInterface $request,
        ThemeProviderInterface $themeProvider,
        SettingsProviderInterface $landingUrlProvider,
        IsStoreIntegratedServiceInterface $isStoreIntegratedService,
    ) {
        $this->request = $request;
        $this->themeProvider = $themeProvider;
        $this->landingUrlProvider = $landingUrlProvider;
        $this->isStoreIntegratedService = $isStoreIntegratedService;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (!$this->isSearchResults($observer)) {
            return;
        }
        $this->isKlevuThemeOrPreview()
            ? $this->addKlevuHandleSearchResults($observer)
            : $this->addMagentoHandleSearchResults($observer);
    }

    /**
     * @param Observer $observer
     *
     * @return bool
     */
    private function isSearchResults(Observer $observer): bool
    {
        $action = $observer->getData('full_action_name');
        $landing = 'catalogsearch';
        try {
            $landingProvider = ltrim(string: $this->landingUrlProvider->get(), characters: '/');
            $landingPaths = explode('/', $landingProvider);
            $landing .= isset($landingPaths[1])
                ? '_' . $landingPaths[1]
                : '_index';
        } catch (OutputDisabledException) {
            // default magento route
            $landing .= '_result';
        }

        return str_starts_with(
            haystack: $action,
            needle: str_replace('/', '_', $landing) . "_index",
        );
    }

    /**
     * @return bool
     */
    private function isKlevuThemeOrPreview(): bool
    {
        if (!$this->isStoreIntegratedService->execute()) {
            return false;
        }
        $isKlevuTheme = $this->themeProvider->isKlevuTheme();
        $klevuPreviewParam = $this->request->getParam(static::REQUEST_PARAM_KLEVU_SRLP_LAYOUT_PREVIEW);
        if (
            $isKlevuTheme
            && $klevuPreviewParam
            && $klevuPreviewParam !== self::PARAM_KLEVU_THEME
        ) {
            return false;
        }

        return $isKlevuTheme
            || $klevuPreviewParam === self::PARAM_KLEVU_THEME;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    private function addKlevuHandleSearchResults(Observer $observer): void
    {
        $layout = $observer->getData('layout');
        if (!($layout instanceof LayoutInterface)) {
            return;
        }
        $update = $layout->getUpdate();
        $update->removeHandle(self::LAYOUT_HANDLE_SEARCH_RESULTS_MAGENTO);
        $update->addHandle(self::LAYOUT_HANDLE_SEARCH_RESULTS_KLEVU);
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    private function addMagentoHandleSearchResults(Observer $observer): void
    {
        $layout = $observer->getData('layout');
        if (!($layout instanceof LayoutInterface)) {
            return;
        }
        $update = $layout->getUpdate();
        $update->removeHandle(self::LAYOUT_HANDLE_SEARCH_RESULTS_KLEVU);
        $update->addHandle(self::LAYOUT_HANDLE_SEARCH_RESULTS_MAGENTO);
    }
}
