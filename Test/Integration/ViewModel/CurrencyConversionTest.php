<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\ViewModel;

use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Klevu\Frontend\ViewModel\CurrencyConversion;
use Klevu\FrontendApi\ViewModel\CurrencyConversionInterface;
use Klevu\FrontendSearch\Constants;
use Klevu\FrontendSearch\Model\Config\Source\ThemeOptionSource;
use Klevu\FrontendSearch\Service\Provider\ThemeProvider;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\SetAuthKeysTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @covers CurrencyConversion
 * @method CurrencyConversionInterface instantiateTestObject(?array $arguments = null)
 * @method CurrencyConversionInterface instantiateTestObjectFromInterface(?array $arguments = null)
 * @magentoAppArea frontend
 */
class CurrencyConversionTest extends TestCase
{
    use ObjectInstantiationTrait;
    use SetAuthKeysTrait;
    use StoreTrait;
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

        $this->implementationFqcn = CurrencyConversion::class;
        $this->interfaceFqcn = CurrencyConversionInterface::class;
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

    public function testIsEnabled_ReturnsTrue_WhenStoreIntegrated_AndQuickEnabled(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());

        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-123456789',
            restAuthKey: '1234567890ABCDEFGHI',
        );

        ConfigFixture::setForStore(
            path: Constants::XML_PATH_QUICK_ENABLED,
            value: 1,
            storeCode: $storeFixture->getCode(),
        );

        $viewModel = $this->instantiateTestObject();
        $this->assertTrue(condition: $viewModel->isEnabled());
    }

    public function testIsEnabled_ReturnsTrue_WhenStoreIntegrated_AndSrlpEnabled(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());

        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-123456789',
            restAuthKey: '1234567890ABCDEFGHI',
        );

        ConfigFixture::setForStore(
            path: ThemeProvider::XML_PATH_SEARCH_THEME,
            value: ThemeOptionSource::SRLP_THEME_VALUE_KLEVU,
            storeCode: $storeFixture->getCode(),
        );

        $viewModel = $this->instantiateTestObject();
        $this->assertTrue(condition: $viewModel->isEnabled());
    }
}
