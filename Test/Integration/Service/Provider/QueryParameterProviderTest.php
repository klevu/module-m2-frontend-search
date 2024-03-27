<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\Service\Provider;

use Klevu\Frontend\Service\Provider\SettingsProvider;
use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Klevu\FrontendSearch\Service\Provider\QueryParameterProvider as QueryParameterProviderVirtualType;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Klevu\FrontendSearch\Service\Provider\QueryParameterProvider
 * @magentoAppArea frontend
 */
class QueryParameterProviderTest extends TestCase
{
    use ObjectInstantiationTrait;
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

        $this->implementationFqcn = QueryParameterProviderVirtualType::class; // @phpstan-ignore-line
        $this->interfaceFqcn = SettingsProviderInterface::class;
        $this->implementationForVirtualType = SettingsProvider::class;
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testGet_ReturnsDefault_WhenNoDataSet(): void
    {
        $provider = $this->instantiateTestObject();
        $this->assertSame(
            expected: 'q',
            actual: $provider->get(),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_frontend/quick_search/search_query_parameter qry
     * @magentoConfigFixture default_store klevu_frontend/quick_search/search_query_parameter qry
     */
    public function testGet_ReturnsCoreConfigData(): void
    {
        $provider = $this->instantiateTestObject();
        $this->assertSame(
            expected: 'qry',
            actual: $provider->get(),
        );
    }

    // phpcs:disable Generic.Files.LineLength.TooLong
    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_frontend/quick_search/search_query_parameter qry
     * @magentoConfigFixture default_store klevu_frontend/quick_search/search_query_parameter qry
     * @magentoConfigFixture default/klevu_frontend/general/klevu_settings {"_1692346616427_427":{"path":"url.queryParam","type":"3","value":"query"}}
     * @magentoConfigFixture default_store klevu_frontend/general/klevu_settings {"_1692346616427_427":{"path":"url.queryParam","type":"3","value":"query"}}
     */
    public function testGet_ReturnsCustomSetting_OverCoreConfigData(): void
    {
        // phpcs:enable Generic.Files.LineLength.TooLong
        $provider = $this->instantiateTestObject();
        $this->assertSame(
            expected: 'query',
            actual: $provider->get(),
        );
    }

    // phpcs:disable Generic.Files.LineLength.TooLong
    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_frontend/quick_search/search_query_parameter qry
     * @magentoConfigFixture default_store klevu_frontend/quick_search/search_query_parameter qry
     * @magentoConfigFixture default/klevu_frontend/general/klevu_settings {"_1692346616427_427":{"path":"url.queryParam","type":"1","value":"query"}}
     * @magentoConfigFixture default_store klevu_frontend/general/klevu_settings {"_1692346616427_427":{"path":"url.queryParam","type":"1","value":"query"}}
     */
    public function testGet_ReturnsCoreConfigData_WhenCustomSettingBoolean(): void
    {
        // phpcs:enable Generic.Files.LineLength.TooLong
        $mockLogger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $mockLogger->expects($this->once())
            ->method('error')
            ->with(
                'Method: {method}, Error {error}',
                [
                    'method' => 'Klevu\Frontend\Service\Provider\SettingsProvider::get',
                    'error' => sprintf(
                        'Invalid Type set for path %s in Jsv2 Custom Settings. Expected String, received %s.',
                        'url.queryParam',
                        'bool',
                    ),
                ],
            );

        $provider = $this->instantiateTestObject([
            'logger' => $mockLogger,
        ]);
        $this->assertSame(
            expected: 'qry',
            actual: $provider->get(),
        );
    }

    // phpcs:disable Generic.Files.LineLength.TooLong
    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_frontend/quick_search/search_query_parameter qry
     * @magentoConfigFixture default_store klevu_frontend/quick_search/search_query_parameter qry
     * @magentoConfigFixture default/klevu_frontend/general/klevu_settings {"_1692346616427_427":{"path":"url.queryParam","type":"2","value":"query"}}
     * @magentoConfigFixture default_store klevu_frontend/general/klevu_settings {"_1692346616427_427":{"path":"url.queryParam","type":"2","value":"query"}}
     */
    public function testGet_ReturnsCoreConfigData_WhenCustomSettingInt(): void
    {
        // phpcs:enable Generic.Files.LineLength.TooLong
        $mockLogger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $mockLogger->expects($this->once())
            ->method('error')
            ->with(
                'Method: {method}, Error {error}',
                [
                    'method' => 'Klevu\Frontend\Service\Provider\SettingsProvider::get',
                    'error' => sprintf(
                        'Invalid Type set for path %s in Jsv2 Custom Settings. Expected String, received %s.',
                        'url.queryParam',
                        'int',
                    ),
                ],
            );

        $provider = $this->instantiateTestObject([
            'logger' => $mockLogger,
        ]);
        $this->assertSame(
            expected: 'qry',
            actual: $provider->get(),
        );
    }
}
