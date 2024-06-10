<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\ViewModel\Html\Head;

use Klevu\Frontend\Exception\OutputDisabledException;
use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class SearchBoxTarget implements ArgumentInterface
{
    public const SEARCH_BOX_TARGET_DEFAULT_VALUE = '#klevuSearchResults';

    /**
     * @var SettingsProviderInterface
     */
    private readonly SettingsProviderInterface $searchBoxTargetProvider;
    /**
     * @var string
     */
    private readonly string $defaultValue;

    /**
     * @param SettingsProviderInterface $searchBoxTargetProvider
     * @param string $defaultValue
     */
    public function __construct(
        SettingsProviderInterface $searchBoxTargetProvider,
        string $defaultValue = self::SEARCH_BOX_TARGET_DEFAULT_VALUE,
    ) {
        $this->searchBoxTargetProvider = $searchBoxTargetProvider;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return string|null
     */
    public function getSearchBoxTargetId(): string|null
    {
        $searchBoxTarget = null;
        try {
            $searchBoxTarget = $this->searchBoxTargetProvider->get();
        } catch (OutputDisabledException) { //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            // this is fine, return the default instead
        }
        $searchBoxTarget ??= $this->defaultValue;

        return str_replace('#', '', $searchBoxTarget);
    }
}
