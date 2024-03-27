<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\Controller\Result;

use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\SetAuthKeysTrait;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Response as TestFrameworkResponse;
use Magento\TestFramework\TestCase\AbstractController;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @covers \Klevu\FrontendSearch\Controller\Result\Index
 * @magentoAppArea frontend
 */
class IndexControllerTest extends AbstractController
{
    use SetAuthKeysTrait;
    use StoreTrait;

    /**
     * @var string
     */
    private string $uri = 'catalogsearch/result';
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

    public function test_PageTitleIsRendered_QueryParamDefault(): void
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

        $request = $this->getRequest();
        $request->setParams([
            'query' => 'socks',
            'qry' => 'shirt',
            'q' => 'jacket',
        ]);

        $this->dispatch($this->uri);
        /** @var TestFrameworkResponse $response */
        $response = $this->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $responseBody = $response->getBody();

        $queryString = 'jacket';
        $this->assertPageTitleIsSet($responseBody, $queryString);
    }

    public function test_PageTitleIsRendered_QueryParamSetViaAdminField(): void
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
        ConfigFixture::setForStore(
            path: 'klevu_frontend/quick_search/search_query_parameter',
            value: 'qry',
            storeCode: $storeFixture->getCode(),
        );

        $request = $this->getRequest();
        $request->setParams([
            'query' => 'socks',
            'qry' => 'shirt',
            'q' => 'jacket',
        ]);

        $this->dispatch($this->uri);
        /** @var TestFrameworkResponse $response */
        $response = $this->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $responseBody = $response->getBody();

        $queryString = 'shirt';
        $this->assertPageTitleIsSet($responseBody, $queryString);
    }

    public function test_PageTitleIsRendered_QueryParamSetViaDynamicRows(): void
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
        ConfigFixture::setForStore(
            path: 'klevu_frontend/quick_search/search_query_parameter',
            value: 'qry',
            storeCode: $storeFixture->getCode(),
        );
        ConfigFixture::setForStore(
            path: 'klevu_frontend/general/klevu_settings',
            value: '{"_1692346616427_427":{"path":"url.queryParam","type":"3","value":"query"}}',
            storeCode: $storeFixture->getCode(),
        );

        $request = $this->getRequest();
        $request->setParams([
            'q' => 'jacket',
            'query' => 'socks',
            'qry' => 'shirt',
        ]);

        $this->dispatch($this->uri);
        /** @var TestFrameworkResponse $response */
        $response = $this->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $responseBody = $response->getBody();

        $queryString = 'socks';
        $this->assertPageTitleIsSet($responseBody, $queryString);
    }

    public function testLayout_PreviewKlevuTheme(): void
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
            value: 0,
            storeCode: $storeFixture->getCode(),
        );

        $request = $this->getRequest();
        $request->setParams([
            'q' => 'jacket',
            'klevu_layout_preview' => 'klevu',
        ]);

        $this->dispatch($this->uri);
        /** @var TestFrameworkResponse $response */
        $response = $this->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $responseBody = $response->getBody();

        $pattern = '#<div[.\s]*class="klevuLanding[.\s]*"[.\s]*><[.\s]*/div>#';
        $matches = [];
        preg_match(
            pattern: $pattern,
            subject: $responseBody,
            matches: $matches,
        );
        $this->assertCount(
            expectedCount: 1,
            haystack: $matches,
            message: 'Klevu JSv2 SRLP Theme script is added',
        );

        // phpcs:ignore Generic.Files.LineLength.TooLong
        $pattern = '#<script[.\s]*type="text&\#x2F;javascript"[.\s]*src="https&\#x3A;&\#x2F;&\#x2F;js\.klevu\.com&\#x2F;theme&\#x2F;default&\#x2F;v2&\#x2F;landing-page-theme\.js"[.\s]*>[.\s]*</script>#';

        $matches = [];
        preg_match(
            pattern: $pattern,
            subject: $responseBody,
            matches: $matches,
        );
        $this->assertCount(
            expectedCount: 1,
            haystack: $matches,
            message: 'Klevu JSv2 SRLP Script Added',
        );
    }

    public function testLayout_PreviewKlevuTheme_NotIntegrated(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');

        ConfigFixture::setForStore(
            path: 'klevu_frontend/srlp/theme',
            value: 1,
            storeCode: $storeFixture->getCode(),
        );

        $request = $this->getRequest();
        $request->setParams([
            'q' => 'jacket',
            'klevu_layout_preview' => 'klevu',
        ]);

        $this->dispatch($this->uri);
        /** @var TestFrameworkResponse $response */
        $response = $this->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $responseBody = $response->getBody();

        $pattern = '#<div[.\s]*class="klevuLanding[.\s]*"[.\s]*><[.\s]*/div>#';
        $matches = [];
        preg_match(
            pattern: $pattern,
            subject: $responseBody,
            matches: $matches,
        );
        $this->assertCount(
            expectedCount: 0,
            haystack: $matches,
            message: 'Klevu JSv2 SRLP Theme script is not added',
        );

        // phpcs:ignore Generic.Files.LineLength.TooLong
        $pattern = '#<script[.\s]*type="text&\#x2F;javascript"[.\s]*src="https&\#x3A;&\#x2F;&\#x2F;js\.klevu\.com&\#x2F;theme&\#x2F;default&\#x2F;v2&\#x2F;landing-page-theme\.js"[.\s]*>[.\s]*</script>#';

        $matches = [];
        preg_match(
            pattern: $pattern,
            subject: $responseBody,
            matches: $matches,
        );
        $this->assertCount(
            expectedCount: 0,
            haystack: $matches,
            message: 'Klevu JSv2 SRLP Script Not Added',
        );
    }

    public function testLayout_PreviewMagentoTheme(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->create(ScopeProviderInterface::class);
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

        $request = $this->getRequest();
        $request->setParams([
            'q' => 'jacket',
            'klevu_layout_preview' => 'native',
        ]);

        $this->dispatch($this->uri);
        /** @var TestFrameworkResponse $response */
        $response = $this->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $responseBody = $response->getBody();

        $pattern = '#<div[.\s]*class="klevuLanding[.\s]*"[.\s]*><[.\s]*/div>#';

        $matches = [];
        preg_match(
            pattern: $pattern,
            subject: $responseBody,
            matches: $matches,
        );
        $this->assertCount(
            expectedCount: 0,
            haystack: $matches,
            message: 'Klevu JSv2 Container Loaded Not Loaded',
        );
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $pattern = '#<script[.\s]*type="text&\#x2F;javascript"[.\s]*src="https&\#x3A;&\#x2F;&\#x2F;js\.klevu\.com&\#x2F;theme&\#x2F;default&\#x2F;v2&\#x2F;landing-page-theme\.js"[.\s]*>[.\s]*</script>#';
        $matches = [];
        preg_match(
            pattern: $pattern,
            subject: $responseBody,
            matches: $matches,
        );
        $this->assertCount(
            expectedCount: 0,
            haystack: $matches,
            message: 'Klevu JSv2 SRLP Script not Added',
        );
    }

    /**
     * @param string $responseBody
     * @param string $queryString
     *
     * @return void
     */
    private function assertPageTitleIsSet(string $responseBody, string $queryString): void
    {
        $pattern = '#<h1[.\s]*class="page-title"[.\s]*>[.\s]*';
        $pattern .= '<span[.\s]*class="base"[.\s]*data-ui-id="page-title-wrapper"[.\s]*>[.\s]*';
        $pattern .= 'Search results for: &\#039;' . $queryString . '&\#039;';
        $pattern .= '[.\s]*</span>[.\s]*</h1>#';

        $matches = [];
        preg_match(
            pattern: $pattern,
            subject: $responseBody,
            matches: $matches,
        );
        $this->assertCount(
            expectedCount: 1,
            haystack: $matches,
            message: 'Page Title Set',
        );
    }
}
