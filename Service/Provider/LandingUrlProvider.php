<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Service\Provider;

use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Magento\Framework\App\Route\ConfigInterface;

class LandingUrlProvider implements SettingsProviderInterface
{
    private const ROUTE_ID_SRLP = 'catalogsearch';
    private const ROUTE_SCOPE_FRONTNAME = 'frontend';

    /**
     * @var ConfigInterface
     */
    private readonly ConfigInterface $config;
    /**
     * @var string
     */
    private readonly string $controllerName;

    /**
     * @param ConfigInterface $config
     * @param string|null $controllerName
     */
    public function __construct(
        ConfigInterface $config,
        ?string $controllerName = '',
    ) {
        $this->config = $config;
        $this->controllerName = trim(
            string: (string)$controllerName,
            characters: ' /',
        );
    }

    /**
     * @return string
     */
    public function get(): string
    {
        $url = $this->config->getRouteFrontName(
            routeId: self::ROUTE_ID_SRLP,
            scope: self::ROUTE_SCOPE_FRONTNAME,
        );
        $append = $this->controllerName
            ? '/' . $this->controllerName
            : '';

        return '/' . trim(string: $url, characters: ' /') . $append;
    }
}
