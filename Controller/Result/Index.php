<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Controller\Result;

use Klevu\Frontend\Exception\InvalidIsEnabledDeterminerException;
use Klevu\Frontend\Exception\OutputDisabledException;
use Klevu\FrontendApi\Service\IsEnabledDeterminerInterface;
use Klevu\FrontendSearch\Service\Provider\QueryTextProviderInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\CatalogSearch\Controller\Result\Index as CatalogSearchResultIndex;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Index extends CatalogSearchResultIndex
{
    /**
     * @var QueryTextProviderInterface
     */
    private readonly QueryTextProviderInterface $queryTextProvider;
    /**
     * @var Escaper
     */
    private readonly Escaper $escaper;
    /**
     * @var IsEnabledDeterminerInterface
     */
    private readonly IsEnabledDeterminerInterface $isEnabledDeterminer;
    /**
     * @var LoggerInterface
     */
    private readonly LoggerInterface $logger;
    /**
     * @var AppState
     */
    private readonly AppState $appState;
    /**
     * @var mixed[]
     */
    private readonly array $isEnabledConditions;

    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     * @param QueryTextProviderInterface $queryTextProvider
     * @param Escaper $escaper
     * @param LoggerInterface $logger
     * @param AppState $appState
     * @param IsEnabledDeterminerInterface $isEnabledDeterminer
     * @param mixed[] $isEnabledConditions
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        QueryTextProviderInterface $queryTextProvider,
        Escaper $escaper,
        LoggerInterface $logger,
        AppState $appState,
        IsEnabledDeterminerInterface $isEnabledDeterminer,
        array $isEnabledConditions = [],
    ) {
        parent::__construct(
            context: $context,
            catalogSession: $catalogSession,
            storeManager: $storeManager,
            queryFactory: $queryFactory,
            layerResolver: $layerResolver,
        );

        $this->queryTextProvider = $queryTextProvider;
        $this->escaper = $escaper;
        $this->logger = $logger;
        $this->appState = $appState;
        $this->isEnabledDeterminer = $isEnabledDeterminer;
        $this->isEnabledConditions = $isEnabledConditions;
    }

    // phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
    /**
     * @return void
     * @throws LocalizedException
     */
    public function execute() // @phpstan-ignore-line
    {
        if (!$this->isKlevuEnabled()) {
            parent::execute();

            return;
        }
        // phpcs:enable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
        $this->setPageTitle();
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    private function setPageTitle(): void
    {
        $queryText = $this->queryTextProvider->get();
        $this->_view->loadLayout();
        $page = $this->_view->getPage();
        $config = $page->getConfig();
        $title = $config->getTitle();
        $title->set(
            __(
                "Search results for: '%1'",
                $this->escaper->escapeHtml($queryText),
            )->render(),
        );
    }

    /**
     * @return bool
     * @throws InvalidIsEnabledDeterminerException
     */
    private function isKlevuEnabled(): bool
    {
        $return = false;
        try {
            $this->isEnabledDeterminer->executeAnd($this->isEnabledConditions);
            $return = true;
        } catch (InvalidIsEnabledDeterminerException $exception) {
            if ($this->appState->getMode() !== AppState::MODE_PRODUCTION) {
                throw $exception;
            }
            $this->logger->error(
                message: 'Method: {method}, Error: {error}',
                context: [
                    'method' => __METHOD__,
                    'error' => $exception->getMessage(),
                ],
            );
        } catch (OutputDisabledException) { //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            // Output is disabled.
        }

        return $return;
    }
}
