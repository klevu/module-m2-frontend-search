<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ThemeOptionSource implements OptionSourceInterface
{
    public const SRLP_THEME_VALUE_DISABLED = 0;
    public const SRLP_THEME_VALUE_KLEVU = 1;

    /**
     * @return mixed[][]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => static::SRLP_THEME_VALUE_DISABLED,
                'label' => __('Native Magento'),
            ],
            [
                'value' => static::SRLP_THEME_VALUE_KLEVU,
                'label' => __('Klevu JS Theme'),
            ],
        ];
    }
}
