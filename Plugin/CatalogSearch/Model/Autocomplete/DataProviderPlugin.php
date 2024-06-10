<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Plugin\CatalogSearch\Model\Autocomplete;

use Klevu\Frontend\Exception\OutputDisabledException;
use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Magento\CatalogSearch\Model\Autocomplete\DataProvider;
use Magento\Search\Model\Autocomplete\ItemInterface;

class DataProviderPlugin
{
    /**
     * @var SettingsProviderInterface
     */
    private readonly SettingsProviderInterface $quickSearchEnabledProvider;

    /**
     * @param SettingsProviderInterface $quickSearchEnabledProvider
     */
    public function __construct(SettingsProviderInterface $quickSearchEnabledProvider)
    {
        $this->quickSearchEnabledProvider = $quickSearchEnabledProvider;
    }

    /**
     * @param DataProvider $subject
     * @param ItemInterface[] $result
     *
     * @return ItemInterface[]
     */
    public function afterGetItems(
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        DataProvider $subject,
        array $result,
    ): array {
        if ($this->isQuickSearchEnabled()) {
            $result = [];
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function isQuickSearchEnabled(): bool
    {
        try {
            $quickSearchEnabled = $this->quickSearchEnabledProvider->get();
        } catch (OutputDisabledException) {
            // in reality this will never be thrown
            $quickSearchEnabled = false;
        }

        return $quickSearchEnabled;
    }
}
