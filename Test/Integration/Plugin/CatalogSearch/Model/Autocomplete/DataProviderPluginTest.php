<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\Plugin\CatalogSearch\Model\Autocomplete;

use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Klevu\FrontendSearch\Constants;
use Klevu\FrontendSearch\Plugin\CatalogSearch\Model\Autocomplete\DataProviderPlugin;
use Klevu\TestFixtures\Catalog\CatalogSearch\SearchQueryFixturePool;
use Klevu\TestFixtures\Catalog\SearchQueryTrait;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Magento\CatalogSearch\Model\Autocomplete\DataProvider as AutoCompleteDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @covers DataProviderPlugin
 * @method DataProviderPlugin instantiateTestObject(?array $arguments = null)
 * @method DataProviderPlugin instantiateTestObjectFromInterface(?array $arguments = null)
 * @magentoAppArea frontend
 */
class DataProviderPluginTest extends TestCase
{
    use ObjectInstantiationTrait;
    use SearchQueryTrait;
    use StoreTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;
    /**
     * @var string|null
     */
    private ?string $pluginName = 'Klevu_FrontendSearch::CatalogSearchModelAutocompleteDataProviderPlugin';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->implementationFqcn = DataProviderPlugin::class;
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeFixturesPool = $this->objectManager->get(StoreFixturesPool::class);
        $this->searchQueryFixturePool = $this->objectManager->get(SearchQueryFixturePool::class);
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->searchQueryFixturePool->rollback();
        $this->storeFixturesPool->rollback();
    }

    /**
     * @magentoAppArea global
     */
    public function testPlugin_DoesNotInterceptCalls_InGlobalArea(): void
    {
        $pluginInfo = $this->getSystemConfigPluginInfo();
        $this->assertArrayNotHasKey($this->pluginName, $pluginInfo);
    }

    public function testPlugin_InterceptCalls_InFrontendArea(): void
    {
        $pluginInfo = $this->getSystemConfigPluginInfo();
        $this->assertArrayHasKey($this->pluginName, $pluginInfo);
        $this->assertSame(DataProviderPlugin::class, $pluginInfo[$this->pluginName]['instance']);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetItems_ReturnsArrayOfSuggestions_WhenKlevuQuickSearchDisabled(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore($storeFixture->get());
        ConfigFixture::setForStore(
            path: Constants::XML_PATH_QUICK_ENABLED,
            value: 0,
            storeCode: $storeFixture->getCode(),
        );

        $queryText = 'Some Query String';

        $request = $this->objectManager->get(RequestInterface::class);
        $request->setParams(['q' => $queryText]);

        $this->createSearchQuery([
            'query_text' => $queryText,
            'store_id' => $storeFixture->getId(),
        ]);

        $provider = $this->objectManager->get(AutoCompleteDataProvider::class);
        $result = $provider->getItems();

        $this->assertNotCount(expectedCount: 0, haystack: $result);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetItems_ReturnsEmptyArray_WhenKlevuQuickSearchEnabled(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore($storeFixture->get());
        ConfigFixture::setForStore(
            path: Constants::XML_PATH_QUICK_ENABLED,
            value: 1,
            storeCode: $storeFixture->getCode(),
        );

        $queryText = 'Another Query String';

        $request = $this->objectManager->get(RequestInterface::class);
        $request->setParams(['q' => $queryText]);

        $this->createSearchQuery([
            'query_text' => $queryText,
            'store_id' => $storeFixture->getId(),
        ]);

        $provider = $this->objectManager->get(AutoCompleteDataProvider::class);
        $result = $provider->getItems();

        $this->assertCount(expectedCount: 0, haystack: $result);
    }

    /**
     * @return mixed[]|null
     */
    private function getSystemConfigPluginInfo(): ?array
    {
        /** @var PluginList $pluginList */
        $pluginList = $this->objectManager->get(PluginList::class);

        return $pluginList->get(AutoCompleteDataProvider::class, []);
    }
}
