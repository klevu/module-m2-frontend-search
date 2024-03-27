<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Service\IsEnabledCondition;

use Klevu\Frontend\Exception\OutputDisabledException;
use Klevu\FrontendApi\Service\IsEnabledCondition\IsEnabledConditionInterface;
use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;

class IsQuickSearchEnabledCondition implements IsEnabledConditionInterface
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
     * @return bool
     * @throws OutputDisabledException
     */
    public function execute(): bool
    {
        return (bool)$this->quickSearchEnabledProvider->get();
    }
}
