<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\ViewModel\Html\Head;

use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Klevu\FrontendSearch\ViewModel\Html\Head\SearchForm;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\SetAuthKeysTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @covers SearchForm
 * @method SearchForm instantiateTestObject(?array $arguments = null)
 * @method SearchForm instantiateTestObjectFromInterface(?array $arguments = null)
 * @magentoAppArea frontend
 */
class SearchFormTest extends TestCase
{
    use ObjectInstantiationTrait;
    use SetAuthKeysTrait;
    use StoreTrait;
    use TestImplementsInterfaceTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null; // @phpstan-ignore-line

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->implementationFqcn = SearchForm::class;
        $this->interfaceFqcn = ArgumentInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeFixturesPool = $this->objectManager->get(StoreFixturesPool::class);
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->storeFixturesPool->rollback();
    }

    public function testIsKlevuQuickSearchEnabled_returnsFalse_WhenStoreNotIntegrated(): void
    {
        $viewModel = $this->instantiateTestObject();
        $this->assertFalse($viewModel->isKlevuQuickSearchEnabled());
    }

    public function testIsKlevuQuickSearchEnabled_returnsFalse_WhenStoreIntegrated_QuickSearchDisabled(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get();
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());

        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-js-key',
            restAuthKey: 'klevu-rest-key',
        );
        ConfigFixture::setForStore(
            path: 'klevu_frontend/quick_search/enabled',
            value: 0,
            storeCode: $storeFixture->getCode(),
        );

        $viewModel = $this->instantiateTestObject();
        $this->assertFalse($viewModel->isKlevuQuickSearchEnabled());
    }

    public function testIsKlevuQuickSearchEnabled_returnsTrue_WhenStoreIntegrated_QuickSearchDisabled(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get();
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());

        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-js-key',
            restAuthKey: 'klevu-rest-key',
        );
        ConfigFixture::setForStore(
            path: 'klevu_frontend/quick_search/enabled',
            value: 1,
            storeCode: $storeFixture->getCode(),
        );

        $viewModel = $this->instantiateTestObject();
        $this->assertTrue($viewModel->isKlevuQuickSearchEnabled());
    }
}
