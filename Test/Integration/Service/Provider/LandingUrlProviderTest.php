<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\Service\Provider;

use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;
use Klevu\FrontendSearch\Service\Provider\LandingUrlProvider;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klevu\FrontendSearch\Service\Provider\LandingUrlProvider;
 * @magentoAppArea frontend
 */
class LandingUrlProviderTest extends TestCase
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

        $this->implementationFqcn = LandingUrlProvider::class;
        $this->interfaceFqcn = SettingsProviderInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testGet_ReturnsLandingPageUrl(): void
    {
        $provider = $this->instantiateTestObject();
        $this->assertSame(
            expected: '/catalogsearch/result',
            actual: $provider->get(),
        );
    }

    public function testGet_ReturnsLandingPageUrl_WithCustomControllerName(): void
    {
        $provider = $this->instantiateTestObject([
            'controllerName' => 'test-controller-name',
        ]);
        $this->assertSame(
            expected: '/catalogsearch/test-controller-name',
            actual: $provider->get(),
        );
    }

    /**
     * @dataProvider dataProvider_testGet_ReturnsLandingPageUrl_WithOutControllerName
     */
    public function testGet_ReturnsLandingPageUrl_WithOutControllerName(mixed $controllerName): void
    {
        $provider = $this->instantiateTestObject([
            'controllerName' => $controllerName,
        ]);
        $this->assertSame(
            expected: '/catalogsearch',
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
