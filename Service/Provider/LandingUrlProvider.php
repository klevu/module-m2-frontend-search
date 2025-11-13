<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Service\Provider;

use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Laminas\Uri\Http;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\UrlInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var StoreManagerInterface
     */
    private readonly StoreManagerInterface $storeManager;
    /**
     * @var Http
     */
    private readonly Http $uri;
    /**
     * @var UrlInterfaceFactory
     */
    private readonly UrlInterfaceFactory $urlBuilderFactory;

    /**
     * @param ConfigInterface $config
     * @param string|null $controllerName
     * @param StoreManagerInterface|null $storeManager
     * @param Http|null $uri
     * @param UrlInterfaceFactory|null $urlBuilderFactory
     */
    public function __construct(
        ConfigInterface $config,
        ?string $controllerName = '',
        ?StoreManagerInterface $storeManager = null,
        ?Http $uri = null,
        ?UrlInterfaceFactory $urlBuilderFactory = null,
    ) {
        $this->config = $config;
        $this->controllerName = trim(
            string: (string)$controllerName,
            characters: ' /',
        );

        $objectManager = ObjectManager::getInstance();
        $this->storeManager = $storeManager ?? $objectManager->get(StoreManagerInterface::class);
        $this->uri = $uri ?? $objectManager->get(Http::class);
        $this->urlBuilderFactory = $urlBuilderFactory ?? $objectManager->get(UrlInterfaceFactory::class);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function get(): string
    {
        $currentStore = $this->storeManager->getStore();

        /** @var UrlInterface $urlBuilder */
        $urlBuilder = $this->urlBuilderFactory->create();
        $urlBuilder->setScope($currentStore);

        $url = $urlBuilder->getUrl(
            routePath: $this->getRoutePath(),
            routeParams: [],
        );

        $parsedUrl = $this->uri->parse($url);

        return $parsedUrl->getPath();
    }

    /**
     * @return string
     */
    public function getRoutePath(): string
    {
        $routePath = $this->config->getRouteFrontName(
            routeId: self::ROUTE_ID_SRLP,
            scope: self::ROUTE_SCOPE_FRONTNAME,
        );
        if ($this->controllerName) {
            $routePath .= '/' . $this->controllerName;
        }

        return $routePath;
    }
}
