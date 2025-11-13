<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\Service\Provider;

use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Klevu\FrontendSearch\Service\Provider\LandingUrlProvider;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Website\WebsiteFixturesPool;
use Klevu\TestFixtures\Website\WebsiteTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @covers \Klevu\FrontendSearch\Service\Provider\LandingUrlProvider;
 * @magentoAppArea frontend
 */
class LandingUrlProviderTest extends TestCase
{
    use ObjectInstantiationTrait;
    use StoreTrait;
    use TestImplementsInterfaceTrait;
    use WebsiteTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null; // @phpstan-ignore-line
    /**
     * @var StoreManagerInterface|null
     */
    private ?StoreManagerInterface $storeManager = null; // @phpstan-ignore-line

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->implementationFqcn = LandingUrlProvider::class;
        $this->interfaceFqcn = SettingsProviderInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);

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
     * @return mixed[][]
     */
    public static function dataProvider_testGet_ReturnsLandingPageUrl(): array
    {
        return [
            '000x' => [
                'use_seo_rewrites' => false,
                'subdirectory' => '',
                'useStoreCodeInUrl' => false,
                'controllerName' => null,
                'expectedResult' => '/index.php/catalogsearch/',
            ],

            '0000' => [
                'use_seo_rewrites' => false,
                'subdirectory' => '',
                'useStoreCodeInUrl' => false,
                'controllerName' => '',
                'expectedResult' => '/index.php/catalogsearch/',
            ],
            '0001' => [
                'use_seo_rewrites' => false,
                'subdirectory' => '',
                'useStoreCodeInUrl' => false,
                'controllerName' => 'test-controller-name',
                'expectedResult' => '/index.php/catalogsearch/test-controller-name/',
            ],
            '0010' => [
                'use_seo_rewrites' => false,
                'subdirectory' => '',
                'useStoreCodeInUrl' => true,
                'controllerName' => '',
                'expectedResult' => '/index.php/klevu_frontsearch_landurl_store1/catalogsearch/',
            ],
            '0011' => [
                'use_seo_rewrites' => false,
                'subdirectory' => '',
                'useStoreCodeInUrl' => true,
                'controllerName' => 'result',
                'expectedResult' => '/index.php/klevu_frontsearch_landurl_store1/catalogsearch/result/',
            ],
            '0100' => [
                'use_seo_rewrites' => false,
                'subdirectory' => 'phpunit-test/',
                'useStoreCodeInUrl' => false,
                'controllerName' => '',
                'expectedResult' => '/phpunit-test/index.php/catalogsearch/',
            ],
            '101' => [
                'use_seo_rewrites' => false,
                'subdirectory' => 'phpunit-test/',
                'useStoreCodeInUrl' => false,
                'controllerName' => 'result',
                'expectedResult' => '/phpunit-test/index.php/catalogsearch/result/',
            ],
            '0110' => [
                'use_seo_rewrites' => false,
                'subdirectory' => 'phpunit-test/',
                'useStoreCodeInUrl' => true,
                'controllerName' => '',
                'expectedResult' => '/phpunit-test/index.php/klevu_frontsearch_landurl_store1/catalogsearch/',
            ],
            '0111' => [
                'use_seo_rewrites' => false,
                'subdirectory' => 'phpunit-test/',
                'useStoreCodeInUrl' => true,
                'controllerName' => 'result',
                'expectedResult' => '/phpunit-test/index.php/klevu_frontsearch_landurl_store1/catalogsearch/result/',
            ],
            '1000' => [
                'use_seo_rewrites' => true,
                'subdirectory' => '',
                'useStoreCodeInUrl' => false,
                'controllerName' => '',
                'expectedResult' => '/catalogsearch/',
            ],
            '1001' => [
                'use_seo_rewrites' => true,
                'subdirectory' => '',
                'useStoreCodeInUrl' => false,
                'controllerName' => 'controller',
                'expectedResult' => '/catalogsearch/controller/',
            ],
            '1010' => [
                'use_seo_rewrites' => true,
                'subdirectory' => '',
                'useStoreCodeInUrl' => true,
                'controllerName' => '',
                'expectedResult' => '/klevu_frontsearch_landurl_store1/catalogsearch/',
            ],
            '1011' => [
                'use_seo_rewrites' => true,
                'subdirectory' => '',
                'useStoreCodeInUrl' => true,
                'controllerName' => 'result',
                'expectedResult' => '/klevu_frontsearch_landurl_store1/catalogsearch/result/',
            ],
            '1100' => [
                'use_seo_rewrites' => true,
                'subdirectory' => 'phpunit-test/',
                'useStoreCodeInUrl' => false,
                'controllerName' => '',
                'expectedResult' => '/phpunit-test/catalogsearch/',
            ],
            '1101' => [
                'use_seo_rewrites' => true,
                'subdirectory' => 'phpunit-test/',
                'useStoreCodeInUrl' => false,
                'controllerName' => 'result',
                'expectedResult' => '/phpunit-test/catalogsearch/result/',
            ],
            '1110' => [
                'use_seo_rewrites' => true,
                'subdirectory' => 'phpunit-test/',
                'useStoreCodeInUrl' => true,
                'controllerName' => '',
                'expectedResult' => '/phpunit-test/klevu_frontsearch_landurl_store1/catalogsearch/',
            ],
            '1111' => [
                'use_seo_rewrites' => true,
                'subdirectory' => 'phpunit-test/',
                'useStoreCodeInUrl' => true,
                'controllerName' => 'result',
                'expectedResult' => '/phpunit-test/klevu_frontsearch_landurl_store1/catalogsearch/result/',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider_testGet_ReturnsLandingPageUrl
     */
    public function testGet_ReturnsLandingPageUrl(
        bool $useSeoRewrites,
        string $subdirectory,
        bool $useStoreCodeInUrl,
        ?string $controllerName,
        string $expectedResult,
    ): void {
        ConfigFixture::setGlobal(
            path: 'web/seo/use_rewrites',
            value: $useSeoRewrites ? '1' : '0',
        );
        ConfigFixture::setGlobal(
            path: 'web/url/use_store',
            value: $useStoreCodeInUrl ? '1' : '0',
        );
        ConfigFixture::setGlobal(
            path: 'web/unsecure/base_url',
            value: 'http://base.domain-global.test/',
        );
        ConfigFixture::setGlobal(
            path: 'web/unsecure/base_link_url',
            value: 'http://link.domain-global.test/',
        );
        ConfigFixture::setGlobal(
            path: 'web/secure/base_url',
            value: 'https://base.domain-global.test/',
        );
        ConfigFixture::setGlobal(
            path: 'web/secure/base_link_url',
            value: 'https://link.domain-global.test/',
        );
        ConfigFixture::setGlobal(
            path: Store::XML_PATH_STORE_IN_URL,
            value: '0',
        );

        $this->createWebsite(
            websiteData: [
                'key' => 'klevu_frontsearch_landurl_website1',
                'code' => 'klevu__landurl_website1',
                'name' => 'Frontend Search: LandingUrl Provider Website 1',
            ],
        );
        $websiteFixture1 = $this->websiteFixturesPool->get('klevu_frontsearch_landurl_website1');

        $this->createStore(
            storeData: [
                'key' => 'klevu_frontsearch_landurl_store1',
                'code' => 'klevu_frontsearch_landurl_store1',
                'name' => 'Frontend Search: LandingUrl Provider Store 1',
                'website_id' => $websiteFixture1->getId(),
                'is_active' => true,
            ],
        );
        $storeFixture1 = $this->storeFixturesPool->get('klevu_frontsearch_landurl_store1');
        ConfigFixture::setForStore(
            path: 'web/seo/use_rewrites',
            value: $useSeoRewrites ? '1' : '0',
            storeCode: 'klevu_frontsearch_landurl_store1',
        );
        ConfigFixture::setForStore(
            path: 'web/url/use_store',
            value: $useStoreCodeInUrl ? '1' : '0',
            storeCode: 'klevu_frontsearch_landurl_store1',
        );
        ConfigFixture::setForStore(
            path: 'web/unsecure/base_url',
            value: 'http://base.domain1.test/' . $subdirectory,
            storeCode: 'klevu_frontsearch_landurl_store1',
        );
        ConfigFixture::setForStore(
            path: 'web/unsecure/base_link_url',
            value: 'http://link.domain1.test/' . $subdirectory,
            storeCode: 'klevu_frontsearch_landurl_store1',
        );
        ConfigFixture::setForStore(
            path: 'web/secure/base_url',
            value: 'https://base.domain1.test/' . $subdirectory,
            storeCode: 'klevu_frontsearch_landurl_store1',
        );
        ConfigFixture::setForStore(
            path: 'web/secure/base_link_url',
            value: 'https://link.domain1.test/' . $subdirectory,
            storeCode: 'klevu_frontsearch_landurl_store1',
        );

        $this->storeManager->setCurrentStore(
            store: $storeFixture1->getId(),
        );
        $currentStore = $this->storeManager->getStore();
        $this->assertSame(
            expected: (int)$storeFixture1->getId(),
            actual: (int)$currentStore->getId(),
        );

        $provider = $this->instantiateTestObject([
            'controllerName' => $controllerName,
        ]);

        $message = $useSeoRewrites
            ? 'With SEO Rewrites; '
            : 'Without SEO Rewrites; ';
        $message .= $subdirectory
            ? 'With Subdirectory; '
            : 'Without Subdirectory; ';
        $message .= $useStoreCodeInUrl
            ? 'With Store Code in URL; '
            : 'Without Store Code in URL; ';
        $message .= $controllerName
            ? 'With Controller Name'
            : 'Without Controller Name';

        $this->assertSame(
            expected: $expectedResult,
            actual: $provider->get(),
            message: $message,
        );

        $expectedRoute = 'catalogsearch';
        if ($controllerName) {
            $expectedRoute .= '/' . $controllerName;
        }
        $this->assertSame(
            expected: $expectedRoute,
            actual: $provider->getRoutePath(),
        );
    }

    public function testGet_ReturnsLandingPageUrl_DiControllerName(): void
    {
        ConfigFixture::setGlobal(
            path: 'web/seo/use_rewrites',
            value: '1',
        );
        ConfigFixture::setGlobal(
            path: Store::XML_PATH_STORE_IN_URL,
            value: '0',
        );

        $provider = $this->instantiateTestObject();

        $this->assertSame(
            expected: '/catalogsearch/result/',
            actual: $provider->get(),
        );
    }

    /**
     * @dataProvider dataProvider_testGet_ReturnsLandingPageUrl_WithOutControllerName
     */
    public function testGet_ReturnsLandingPageUrl_WithOutControllerName(mixed $controllerName): void
    {
        ConfigFixture::setGlobal(
            path: 'web/seo/use_rewrites',
            value: '1',
        );
        ConfigFixture::setGlobal(
            path: Store::XML_PATH_STORE_IN_URL,
            value: '0',
        );

        $provider = $this->instantiateTestObject([
            'controllerName' => $controllerName,
        ]);
        $this->assertSame(
            expected: '/catalogsearch/',
            actual: $provider->get(),
        );
    }

    /**
     * @return mixed[][]
     */
    public function dataProvider_testGet_ReturnsLandingPageUrl_WithOutControllerName(): array
    {
        return [
            [''],
            ['/'],
            ['//'],
            ['///'],
            ['/ /'],
            [' / / '],
        ];
    }
}
