<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\Service\Provider;

use Klevu\FrontendSearch\Service\Provider\QueryTextProvider;
use Klevu\FrontendSearch\Service\Provider\QueryTextProviderInterface;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Traits\TestInterfacePreferenceTrait;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klevu\FrontendSearch\Service\Provider\QueryTextProvider
 * @magentoAppArea frontend
 */
class QueryTextProviderTest extends TestCase
{
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;
    use TestInterfacePreferenceTrait;

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

        $this->implementationFqcn = QueryTextProvider::class;
        $this->interfaceFqcn = QueryTextProviderInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testGet_ReturnsQueryText(): void
    {
        $mockRequest = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $mockRequest->expects($this->once())
            ->method('getParam')
            ->with('q')
            ->willReturn('jacket');

        $provider = $this->instantiateTestObject([
            'request' => $mockRequest,
        ]);
        $this->assertSame(
            expected: 'jacket',
            actual: $provider->get(),
        );
    }

    /**
     * @magentoConfigFixture default/catalog/search/max_query_length 10
     * @magentoConfigFixture default_store catalog/search/max_query_length 10
     */
    public function testGet_ReturnsQueryText_UpToMaxLength(): void
    {
        $mockRequest = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $mockRequest->expects($this->once())
            ->method('getParam')
            ->with('q')
            ->willReturn('black jackets made of leather');

        $provider = $this->instantiateTestObject([
            'request' => $mockRequest,
        ]);
        $this->assertSame(
            expected: 'black jack',
            actual: $provider->get(),
        );
    }

    /**
     * @magentoConfigFixture default/catalog/search/max_query_length iqefhie
     * @magentoConfigFixture default_store catalog/search/max_query_length iqefhie
     */
    public function testGet_ReturnsQueryText_UpToDefaultMaxLength_WhenMaxLengthNotNumeric(): void
    {
        // 128 character string
        $queryString = 'ugmrlierkqjzxygsdjqjqwomxuphwubvbcpuzbaorqkljpxnoyvdbsmpkgwixltuxzodzoflcohraylsrfvzagwpjzzbjs'
            . 'gyvjynwzbgtxkulbztnjwwttxghdebrfjz';
        $mockRequest = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $mockRequest->expects($this->once())
            ->method('getParam')
            ->with('q')
            ->willReturn($queryString . 'more than 128 characters'); // default length is 128

        $provider = $this->instantiateTestObject([
            'request' => $mockRequest,
        ]);
        $this->assertSame(
            expected: $queryString,
            actual: $provider->get(),
        );
    }

    /**
     * @magentoConfigFixture default/catalog/search/max_query_length -3
     * @magentoConfigFixture default_store catalog/search/max_query_length -3
     */
    public function testGet_ReturnsQueryText_UpToDefaultMaxLength_WhenMaxLengthNegative(): void
    {
        // 128 character string
        $queryString = 'ugmrlierkqjzxygsdjqjqwomxuphwubvbcpuzbaorqkljpxnoyvdbsmpkgwixltuxzodzoflcohraylsrfvzagwpjzzbjs'
            . 'gyvjynwzbgtxkulbztnjwwttxghdebrfjz';
        $mockRequest = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $mockRequest->expects($this->once())
            ->method('getParam')
            ->with('q')
            ->willReturn($queryString . 'more than 128 characters'); // default length is 128

        $provider = $this->instantiateTestObject([
            'request' => $mockRequest,
        ]);
        $this->assertSame(
            expected: $queryString,
            actual: $provider->get(),
        );
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/quick_search/search_query_parameter query
     * @magentoConfigFixture default_store klevu_frontend/quick_search/search_query_parameter query
     */
    public function testGet_ReturnsQueryText_ForNoneDefaultQueryParam(): void
    {
        $mockRequest = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $mockRequest->expects($this->once())
            ->method('getParam')
            ->with('query')
            ->willReturn('white shirt');

        $provider = $this->instantiateTestObject([
            'request' => $mockRequest,
        ]);
        $this->assertSame(
            expected: 'white shirt',
            actual: $provider->get(),
        );
    }

    /**
     * @dataProvider dataProvider_testGet_ReturnsQueryText_WhenRequestIsNotString
     */
    public function testGet_ReturnsQueryText_WhenRequestIsNotString(mixed $requestParam): void
    {
        $mockRequest = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $mockRequest->expects($this->once())
            ->method('getParam')
            ->with('q')
            ->willReturn($requestParam);

        $provider = $this->instantiateTestObject([
            'request' => $mockRequest,
        ]);
        $this->assertSame(
            expected: '',
            actual: $provider->get(),
        );
    }

    /**
     * @return mixed[][]
     */
    public function dataProvider_testGet_ReturnsQueryText_WhenRequestIsNotString(): array
    {
        return [
            [null],
            [false],
            [true],
            [1],
            [12.34],
            [['jacket', 'shirt']],
            [new DataObject()],
        ];
    }
}
