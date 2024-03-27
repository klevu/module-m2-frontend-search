<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\Observer;

use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Klevu\FrontendSearch\Observer\UpdateSearchResultsPageLayout;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\SetAuthKeysTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ConfigInterface as EventConfig;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @covers \Klevu\FrontendSearch\Observer\UpdateSearchResultsPageLayout
 * @magentoAppArea frontend
 */
class UpdateSearchResultsPageLayoutTest extends TestCase
{
    use ObjectInstantiationTrait;
    use SetAuthKeysTrait;
    use StoreTrait;
    use TestImplementsInterfaceTrait;

    private const OBSERVER_NAME = 'Klevu_FrontendSearch_UpdateSearchResultsPageLayout';
    private const EVENT_NAME_LAYOUT_LOAD_BEFORE = 'layout_load_before';
    private const LAYOUT_HANDLE_SEARCH_RESULTS_KLEVU = 'klevu_search_results';
    private const LAYOUT_HANDLE_SEARCH_RESULTS_MAGENTO = 'catalogsearch_result_index';

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->implementationFqcn = UpdateSearchResultsPageLayout::class;
        $this->interfaceFqcn = ObserverInterface::class;
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

    public function testUpdateSearchResultsPageLayoutTestObserver_IsConfigured(): void
    {
        $observerConfig = $this->objectManager->create(type: EventConfig::class);
        $observers = $observerConfig->getObservers(eventName: self::EVENT_NAME_LAYOUT_LOAD_BEFORE);

        $this->assertArrayHasKey(key: self::OBSERVER_NAME, array: $observers);
        $this->assertSame(
            expected: ltrim(string: UpdateSearchResultsPageLayout::class, characters: '\\'),
            actual: $observers[self::OBSERVER_NAME]['instance'],
        );
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/category_navigation/theme 1
     * @magentoConfigFixture default_store klevu_frontend/category_navigation/theme 1
     */
    public function testHandleNotAddedForOtherRoutes(): void
    {
        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest(controller: 'index');

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_KLEVU,
            haystack: $update->getHandles(),
        );
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/srlp/theme 0
     * @magentoConfigFixture default_store klevu_frontend/srlp/theme 0
     */
    public function testMagentoLayout_KlevuThemeDisabledInAdmin(): void
    {
        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_KLEVU,
            haystack: $update->getHandles(),
        );
        $this->assertContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_MAGENTO,
            haystack: $update->getHandles(),
        );
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/srlp/theme 1
     * @magentoConfigFixture default_store klevu_frontend/srlp/theme 1
     */
    public function testMagentoLayout_KlevuThemeEnabledInAdmin_NotIntegrated(): void
    {
        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_KLEVU,
            haystack: $update->getHandles(),
        );
        $this->assertContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_MAGENTO,
            haystack: $update->getHandles(),
        );
    }

    public function testMagentoLayout_KlevuThemeEnabledInAdmin_Integrated(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());

        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-js-key',
            restAuthKey: 'klevu-rest-key',
        );
        ConfigFixture::setForStore(
            path: 'klevu_frontend/srlp/theme',
            value: 1,
            storeCode: $storeFixture->getCode(),
        );

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_KLEVU,
            haystack: $update->getHandles(),
        );
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_MAGENTO,
            haystack: $update->getHandles(),
        );
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/srlp/theme 0
     * @magentoConfigFixture default_store klevu_frontend/srlp/theme 0
     */
    public function testPreviewKlevuLayout_KlevuThemeDisabledInAdmin_RequestParam_NotIntegrated(): void
    {
        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();
        $request->setParams([
            'klevu_layout_preview' => 'klevu',
        ]);

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_KLEVU,
            haystack: $update->getHandles(),
        );
        $this->assertContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_MAGENTO,
            haystack: $update->getHandles(),
        );
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/srlp/theme 0
     * @magentoConfigFixture klevu_test_store_1_store klevu_frontend/srlp/theme 0
     */
    public function testPreviewKlevuLayout_KlevuThemeDisabledInAdmin_RequestParam_Integrated(): void
    {
        $this->createStore();
        $store = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($store->get());

        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-js-key',
            restAuthKey: 'klevu-rest-key',
        );

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();
        $request->setParams([
            'klevu_layout_preview' => 'klevu',
        ]);

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_KLEVU,
            haystack: $update->getHandles(),
        );
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_MAGENTO,
            haystack: $update->getHandles(),
        );
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/srlp/theme 1
     * @magentoConfigFixture default_store klevu_frontend/srlp/theme 1
     */
    public function testPreviewMagentoLayout_KlevuThemeEnabledInAdmin_RequestParam(): void
    {
        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();
        $request->setParams([
            'klevu_layout_preview' => 'native',
        ]);

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_KLEVU,
            haystack: $update->getHandles(),
        );
        $this->assertContains(
            needle: self::LAYOUT_HANDLE_SEARCH_RESULTS_MAGENTO,
            haystack: $update->getHandles(),
        );
    }

    /**
     * @return RequestInterface
     */
    private function setRequest(
        string $route = 'catalogsearch',
        string $controller = 'result',
        string $action = 'index',
    ): mixed {
        $request = $this->objectManager->get(type: RequestInterface::class);
        $request->setRouteName($route);
        $request->setControllerName($controller);
        $request->setActionName($action);

        return $request;
    }

    /**
     * @param RequestInterface $request
     * @param LayoutInterface $layout
     * @param string $event
     *
     * @return void
     */
    private function dispatchEvent(
        RequestInterface $request,
        LayoutInterface $layout,
        string $event,
    ): void {
        /** @var EventManager $eventManager */
        $eventManager = $this->objectManager->get(type: EventManager::class);
        $fullActionName = method_exists($request, 'getFullActionName')
            ? $request->getFullActionName()
            : null;

        $eventManager->dispatch(
            $event,
            [
                'full_action_name' => $fullActionName,
                'layout' => $layout,
            ],
        );
    }
}
