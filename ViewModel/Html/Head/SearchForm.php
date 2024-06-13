<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\ViewModel\Html\Head;

use Klevu\Frontend\Exception\OutputDisabledException;
use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class SearchForm implements ArgumentInterface
{
    /**
     * @var SettingsProviderInterface
     */
    private readonly SettingsProviderInterface $quickSearchEnabledProvider;

    /**
     * @param SettingsProviderInterface $quickSearchEnabledProvider
     */
    public function __construct(
        SettingsProviderInterface $quickSearchEnabledProvider,
    ) {
        $this->quickSearchEnabledProvider = $quickSearchEnabledProvider;
    }

    /**
     * @return bool
     */
    public function isKlevuQuickSearchEnabled(): bool
    {
        $return = false;
        try {
            $return = $this->quickSearchEnabledProvider->get();
        } catch (OutputDisabledException) { //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            // this is never thrown by this provider
        }

        return $return;
    }
}
