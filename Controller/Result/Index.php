<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Controller\Result;

use Klevu\FrontendSearch\Service\Provider\QueryTextProviderInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Escaper;

class Index extends Action implements HttpGetActionInterface, HttpPostActionInterface
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
     * @param Context $context
     * @param QueryTextProviderInterface $queryTextProvider
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        QueryTextProviderInterface $queryTextProvider,
        Escaper $escaper,
    ) {
        parent::__construct($context);

        $this->queryTextProvider = $queryTextProvider;
        $this->escaper = $escaper;
    }

    // phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
    /**
     * @return void
     */
    public function execute() // @phpstan-ignore-line
    {
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
}
