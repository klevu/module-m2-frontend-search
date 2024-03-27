<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\Service\Provider;

use Klevu\Frontend\Service\Provider\SettingsProvider;
use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Klevu\FrontendSearch\Service\Provider\SearchBoxSelectorProvider as SearchBoxSelectorProviderVirtualType;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Klevu\FrontendSearch\Service\Provider\SearchBoxSelectorProvider
 * @magentoAppArea frontend
 */
class SearchBoxSelectorProviderTest extends TestCase
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

        $this->implementationFqcn = SearchBoxSelectorProviderVirtualType::class; // @phpstan-ignore-line
        $this->interfaceFqcn = SettingsProviderInterface::class;
        $this->implementationForVirtualType = SettingsProvider::class;
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testGet_ReturnsDefault_WhenNoDataSet(): void
    {
        $provider = $this->instantiateTestObject();
        $this->assertSame(
            expected: 'input[type=text][name=q],input[type=search][name=q]',
            actual: $provider->get(),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_frontend/quick_search/search_box_selector input[type=search][name=qry]
     * @magentoConfigFixture default_store klevu_frontend/quick_search/search_box_selector input[type=search][name=qry]
     */
    public function testGet_ReturnsCoreConfigData(): void
    {
        $provider = $this->instantiateTestObject();
        $this->assertSame(
            expected: 'input[type=search][name=qry]',
            actual: $provider->get(),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_frontend/quick_search/search_box_selector input[type=search][name=qry]
     * @magentoConfigFixture default_store klevu_frontend/quick_search/search_box_selector input[type=search][name=qry]
     * @magentoConfigFixture default/klevu_frontend/general/klevu_settings {"_1692439469779_779":{"path":"search.searchBoxSelector","type":"3","value":"input[type=text][name=query]"}}
     * @magentoConfigFixture default_store klevu_frontend/general/klevu_settings {"_1692439469779_779":{"path":"search.searchBoxSelector","type":"3","value":"input[type=text][name=query]"}}
     */
    public function testGet_ReturnsCustomSetting_OverCoreConfigData(): void
    {
        $provider = $this->instantiateTestObject();
        $this->assertSame(
            expected: 'input[type=text][name=query]',
            actual: $provider->get(),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_frontend/quick_search/search_box_selector input[type=search][name=qry]
     * @magentoConfigFixture default_store klevu_frontend/quick_search/search_box_selector input[type=search][name=qry]
     * @magentoConfigFixture default/klevu_frontend/general/klevu_settings {"_1692439469779_779":{"path":"search.searchBoxSelector","type":"1","value":"input[type=text][name=query]"}}
     * @magentoConfigFixture default_store klevu_frontend/general/klevu_settings {"_1692439469779_779":{"path":"search.searchBoxSelector","type":"1","value":"input[type=text][name=query]"}}
     */
    public function testGet_ReturnsCoreConfigData_WhenCustomSettingBoolean(): void
    {
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
                        'search.searchBoxSelector',
                        'bool',
                    ),
                ],
            );

        $provider = $this->instantiateTestObject([
            'logger' => $mockLogger,
        ]);
        $this->assertSame(
            expected: 'input[type=search][name=qry]',
            actual: $provider->get(),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/klevu_frontend/quick_search/search_box_selector input[type=search][name=qry]
     * @magentoConfigFixture default_store klevu_frontend/quick_search/search_box_selector input[type=search][name=qry]
     * @magentoConfigFixture default/klevu_frontend/general/klevu_settings {"_1692439469779_779":{"path":"search.searchBoxSelector","type":"2","value":"input[type=text][name=query]"}}
     * @magentoConfigFixture default_store klevu_frontend/general/klevu_settings {"_1692439469779_779":{"path":"search.searchBoxSelector","type":"2","value":"input[type=text][name=query]"}}
     */
    public function testGet_ReturnsCoreConfigData_WhenCustomSettingInt(): void
    {
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
                        'search.searchBoxSelector',
                        'int',
                    ),
                ],
            );

        $provider = $this->instantiateTestObject([
            'logger' => $mockLogger,
        ]);
        $this->assertSame(
            expected: 'input[type=search][name=qry]',
            actual: $provider->get(),
        );
    }
}
