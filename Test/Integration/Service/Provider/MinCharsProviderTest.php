<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\Service\Provider;

use Klevu\Frontend\Service\Provider\SettingsProvider;
use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Klevu\FrontendSearch\Service\Provider\MinCharsProvider as MinCharsProviderVirtualType;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Klevu\FrontendSearch\Service\Provider\MinCharsProvider
 * @magentoAppArea frontend
 */
class MinCharsProviderTest extends TestCase
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

        $this->implementationFqcn = MinCharsProviderVirtualType::class; // @phpstan-ignore-line
        $this->interfaceFqcn = SettingsProviderInterface::class;
        $this->implementationForVirtualType = SettingsProvider::class;
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testGet_ReturnsDefault_WhenNoDataSet(): void
    {
        $provider = $this->instantiateTestObject();
        $this->assertSame(
            expected: 3,
            actual: $provider->get(),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/catalog/search/min_query_length 5
     * @magentoConfigFixture default_store catalog/search/min_query_length 5
     */
    public function testGet_ReturnsCoreConfigData(): void
    {
        $provider = $this->instantiateTestObject();
        $this->assertSame(
            expected: 5,
            actual: $provider->get(),
        );
    }

    // phpcs:disable Generic.Files.LineLength.TooLong
    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/catalog/search/min_query_length 5
     * @magentoConfigFixture default_store catalog/search/min_query_length 5
     * @magentoConfigFixture default/klevu_frontend/general/klevu_settings {"_1692439469779_779":{"path":"search.minChars","type":"2","value":"10"}}
     * @magentoConfigFixture default_store klevu_frontend/general/klevu_settings {"_1692439469779_779":{"path":"search.minChars","type":"2","value":"10"}}
     */
    public function testGet_ReturnsCustomSetting_OverCoreConfigData(): void
    {
        // phpcs:enable Generic.Files.LineLength.TooLong
        $provider = $this->instantiateTestObject();
        $this->assertSame(
            expected: 10,
            actual: $provider->get(),
        );
    }

    // phpcs:disable Generic.Files.LineLength.TooLong
    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/catalog/search/min_query_length 5
     * @magentoConfigFixture default_store catalog/search/min_query_length 5
     * @magentoConfigFixture default/klevu_frontend/general/klevu_settings {"_1692439469779_779":{"path":"search.minChars","type":"1","value":"10"}}
     * @magentoConfigFixture default_store klevu_frontend/general/klevu_settings {"_1692439469779_779":{"path":"search.minChars","type":"1","value":"10"}}
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
                        'Invalid Type set for path %s in Jsv2 Custom Settings. Expected Integer, received %s.',
                        'search.minChars',
                        'bool',
                    ),
                ],
            );

        $provider = $this->instantiateTestObject([
            'logger' => $mockLogger,
        ]);
        $this->assertSame(
            expected: 5,
            actual: $provider->get(),
        );
    }

    // phpcs:disable Generic.Files.LineLength.TooLong
    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/catalog/search/min_query_length 5
     * @magentoConfigFixture default_store catalog/search/min_query_length 5
     * @magentoConfigFixture default/klevu_frontend/general/klevu_settings {"_1692439469779_779":{"path":"search.minChars","type":"3","value":"10"}}
     * @magentoConfigFixture default_store klevu_frontend/general/klevu_settings {"_1692439469779_779":{"path":"search.minChars","type":"3","value":"10"}}
     */
    public function testGet_ReturnsCoreConfigData_WhenCustomSettingString(): void
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
                        'Invalid Type set for path %s in Jsv2 Custom Settings. Expected Integer, received %s.',
                        'search.minChars',
                        'string',
                    ),
                ],
            );

        $provider = $this->instantiateTestObject([
            'logger' => $mockLogger,
        ]);
        $this->assertSame(
            expected: 5,
            actual: $provider->get(),
        );
    }
}
