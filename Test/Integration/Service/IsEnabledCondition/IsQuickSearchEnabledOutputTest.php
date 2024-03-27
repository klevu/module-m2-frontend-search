<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\Service\IsEnabledCondition;

use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\SetAuthKeysTrait;
use Klevu\TestFixtures\Website\WebsiteFixturesPool;
use Klevu\TestFixtures\Website\WebsiteTrait;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Response as TestFrameworkResponse;
use Magento\TestFramework\TestCase\AbstractController;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @covers \Klevu\FrontendSearch\Service\IsEnabledCondition\IsQuickSearchEnabledCondition
 */
class IsQuickSearchEnabledOutputTest extends AbstractController
{
    use SetAuthKeysTrait;
    use StoreTrait;
    use WebsiteTrait;

    /**
     * @var string
     */
    private string $uri = 'catalogsearch/result'; // @phpstan-ignore-line
    /**
     * @var string
     */
    private string $pattern = '#<script[.\s]*type="text&\#x2F;javascript"[.\s]*src="https&\#x3A;&\#x2F;&\#x2F;js\.klevu\.com&\#x2F;theme&\#x2F;default&\#x2F;v2&\#x2F;quick-search-theme.lazyload\.js"[.\s]*>[.\s]*</script>#'; // phpcs:ignore Generic.Files.LineLength.TooLong
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

        $this->objectManager = $this->_objectManager;
        $this->storeFixturesPool = $this->objectManager->get(StoreFixturesPool::class);
        $this->websiteFixturesPool = $this->objectManager->get(WebsiteFixturesPool::class);
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->storeFixturesPool->rollback();
        $this->websiteFixturesPool->rollback();
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/quick_search/enabled 1
     * @magentoConfigFixture default_store klevu_frontend/quick_search/enabled 1
     */
    public function test_QuickSearchJs_IsNotIncluded_WhenStoreNotIntegrated_QuickSearchEnabled(): void
    {
        $this->dispatch('cms/index/index');

        /** @var TestFrameworkResponse $response */
        $response = $this->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $responseBody = $response->getBody();

        $matches = [];
        preg_match(
            pattern: $this->pattern,
            subject: $responseBody,
            matches: $matches,
        );
        $this->assertCount(
            expectedCount: 0,
            haystack: $matches,
            message: 'Klevu JSv2 Quick Search Script Not Added',
        );
    }

    public function test_QuickSearchJs_IsIncluded_WhenStoreIntegrated_QuickSearchEnabled(): void
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
            path: 'klevu_frontend/quick_search/enabled',
            value: 1,
            storeCode: $storeFixture->getCode(),
        );

        $this->dispatch('cms/index/index');

        /** @var TestFrameworkResponse $response */
        $response = $this->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $responseBody = $response->getBody();

        $matches = [];
        preg_match(
            pattern: $this->pattern,
            subject: $responseBody,
            matches: $matches,
        );
        $this->assertCount(
            expectedCount: 1,
            haystack: $matches,
            message: 'Klevu JSv2 Quick Search Script Added',
        );
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/quick_search/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_frontend/quick_search/enabled 0
     */
    public function test_QuickSearchJs_IsNotIncluded_WhenStoreIntegrated_QuickSearchDisabled(): void
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

        $this->dispatch('cms/index/index');

        /** @var TestFrameworkResponse $response */
        $response = $this->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $responseBody = $response->getBody();

        $matches = [];
        preg_match(
            pattern: $this->pattern,
            subject: $responseBody,
            matches: $matches,
        );

        $this->assertCount(
            expectedCount: 0,
            haystack: $matches,
            message: 'Klevu JSv2 Quick Search Script Not Added',
        );
    }

    public function test_QuickSearchJs_IsIncluded_WhenWebsiteIntegrated_QuickSearchEnabled(): void
    {
        $this->markTestSkipped('Skip until website integration is released');
        $this->createWebsite();
        $websiteFixture = $this->websiteFixturesPool->get('test_website');
        $this->createStore([
            'website_id' => $websiteFixture->getId(),
        ]);
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($websiteFixture->get());

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

        $scopeProvider->setCurrentScope($storeFixture->get());
        $this->dispatch('cms/index/index');

        /** @var TestFrameworkResponse $response */
        $response = $this->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $responseBody = $response->getBody();

        $matches = [];
        preg_match(
            pattern: $this->pattern,
            subject: $responseBody,
            matches: $matches,
        );
        $this->assertCount(
            expectedCount: 1,
            haystack: $matches,
            message: 'Klevu JSv2 Quick Search Script Added',
        );
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/quick_search/enabled 1
     * @magentoConfigFixture default_store klevu_frontend/quick_search/enabled 0
     */
    public function test_QuickSearchJs_IsNotIncluded_WhenStoreNotIntegrated_QuickSearchDisabled(): void
    {

        $this->dispatch('cms/index/index');

        /** @var TestFrameworkResponse $response */
        $response = $this->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $responseBody = $response->getBody();

        $matches = [];
        preg_match(
            pattern: $this->pattern,
            subject: $responseBody,
            matches: $matches,
        );

        $this->assertCount(
            expectedCount: 0,
            haystack: $matches,
            message: 'Klevu JSv2 Quick Search Script Not Added',
        );
    }
}
