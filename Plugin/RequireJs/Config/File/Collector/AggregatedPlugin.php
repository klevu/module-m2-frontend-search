<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Plugin\RequireJs\Config\File\Collector;

use Klevu\Frontend\Exception\OutputDisabledException;
use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Magento\Framework\RequireJs\Config\File\Collector\Aggregated;
use Magento\Framework\View\File;

class AggregatedPlugin
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
     * @param Aggregated $subject
     * @param File[] $result
     *
     * @return File[]
     */
    public function afterGetFiles(
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        Aggregated $subject,
        array $result,
    ): array {
        if (!$this->isQuickSearchEnabled()) {
            $result = $this->removeQuickSearchRequireJs($result);
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

    /**
     * @param File[] $result
     *
     * @return File[]
     */
    private function removeQuickSearchRequireJs(array $result): array
    {
        foreach ($result as $key => $file) {
            if ($file->getModule() === "Klevu_FrontendSearch") {
                unset($result[$key]);
            }
        }

        return $result;
    }
}
