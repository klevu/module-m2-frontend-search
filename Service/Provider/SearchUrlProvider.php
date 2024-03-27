<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Service\Provider;

use Klevu\Configuration\Service\Provider\Sdk\BaseUrlsProvider;
use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;

class SearchUrlProvider implements SettingsProviderInterface
{
    /**
     * @var BaseUrlsProvider
     */
    private readonly BaseUrlsProvider $baseUrlsProvider;

    /**
     * @param BaseUrlsProvider $baseUrlsProvider
     */
    public function __construct(BaseUrlsProvider $baseUrlsProvider)
    {
        $this->baseUrlsProvider = $baseUrlsProvider;
    }

    /**
     * @return string|null
     */
    public function get(): ?string
    {
        $searchUrl = $this->baseUrlsProvider->getSearchUrl();
        if (!$searchUrl) {
            return null;
        }
        //phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $urlParts = parse_url($searchUrl);
        $url = ($urlParts['host'] ?? null)
            ? trim($urlParts['host'])
            : trim($urlParts['path']);

        return $url !== ''
            ? 'https://' . $url . '/cs/v2/search'
            : null;
    }
}
