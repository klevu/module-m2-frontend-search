<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Service\Provider;

use Klevu\Frontend\Exception\OutputDisabledException;
use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Search\Model\Query as SearchQuery;
use Magento\Store\Model\ScopeInterface;

class QueryTextProvider implements QueryTextProviderInterface
{
    private const MAX_QUERY_LENGTH_DEFAULT = 128;

    /**
     * @var ScopeConfigInterface
     */
    private readonly ScopeConfigInterface $scopeConfig;
    /**
     * @var SettingsProviderInterface
     */
    private readonly SettingsProviderInterface $queryParameterProvider;
    /**
     * @var RequestInterface
     */
    private readonly RequestInterface $request;
    /**
     * @var StringUtils
     */
    private readonly StringUtils $stringUtils;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SettingsProviderInterface $queryParameterProvider
     * @param RequestInterface $request
     * @param StringUtils $string
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SettingsProviderInterface $queryParameterProvider,
        RequestInterface $request,
        StringUtils $string,
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->queryParameterProvider = $queryParameterProvider;
        $this->request = $request;
        $this->stringUtils = $string;
    }

    /**
     * Returns unescaped text from query. Ensure you escape this if displaying it anywhere
     *
     * @return string
     * @throws OutputDisabledException
     */
    public function get(): string
    {
        return $this->getPreparedQueryText(
            queryText: $this->getQueryText(),
            maxQueryLength: $this->getMaxQueryLength(),
        );
    }

    /**
     * @param string $queryText
     * @param int $maxQueryLength
     *
     * @return string
     */
    private function getPreparedQueryText(string $queryText, int $maxQueryLength): string
    {
        if (
            $this->isQueryTooLong(
                queryText: $queryText,
                maxQueryLength: $maxQueryLength,
            )
        ) {
            $queryText = $this->stringUtils->substr(
                string: $queryText,
                offset: 0,
                length: $maxQueryLength,
            );
        }

        return $queryText;
    }

    /**
     * @return string
     * @throws OutputDisabledException
     */
    private function getQueryText(): string
    {
        $queryParameter = $this->queryParameterProvider->get();
        $queryText = $this->request->getParam(key: $queryParameter);

        return !is_string($queryText)
            ? ''
            : $this->stringUtils->cleanString(string: trim($queryText));
    }

    /**
     * Retrieve maximum query length
     *
     * @param mixed $store
     *
     * @return int
     */
    private function getMaxQueryLength(mixed $store = null): int
    {
        $value = $this->scopeConfig->getValue(
            SearchQuery::XML_PATH_MAX_QUERY_LENGTH,
            ScopeInterface::SCOPE_STORES,
            $store,
        );
        $value = is_numeric($value)
            ? (int)$value
            : self::MAX_QUERY_LENGTH_DEFAULT;

        return $value > 0
            ? $value
            : self::MAX_QUERY_LENGTH_DEFAULT;
    }

    /**
     * @param string $queryText
     * @param int $maxQueryLength
     *
     * @return bool
     */
    private function isQueryTooLong(string $queryText, int $maxQueryLength): bool
    {
        return $this->stringUtils->strlen(string: $queryText) > $maxQueryLength;
    }
}
