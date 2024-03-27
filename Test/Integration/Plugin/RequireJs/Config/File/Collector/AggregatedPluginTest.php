<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendSearch\Test\Integration\Plugin\RequireJs\Config\File\Collector;

use Klevu\FrontendSearch\Constants;
use Klevu\FrontendSearch\Plugin\RequireJs\Config\File\Collector\AggregatedPlugin;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\RequireJs\Config\File\Collector\Aggregated as AggregatedRequireJsFiles;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @covers AggregatedPlugin
 * @method AggregatedPlugin instantiateTestObject(?array $arguments = null)
 * @method AggregatedPlugin instantiateTestObjectFromInterface(?array $arguments = null)
 * @magentoAppArea frontend
 */
class AggregatedPluginTest extends TestCase
{
    use ObjectInstantiationTrait;
    use StoreTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;
    /**
     * @var string|null
     */
    private ?string $pluginName = 'Klevu_FrontendSearch::RequireJsConfigFileCollectorAggregatedPlugin';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->implementationFqcn = AggregatedPlugin::class;
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
        $this->assertSame(AggregatedPlugin::class, $pluginInfo[$this->pluginName]['instance']);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAfterGetFiles_ReturnsFrontendSearchJS_WhenQuickEnabled(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');

        ConfigFixture::setForStore(
            path: Constants::XML_PATH_QUICK_ENABLED,
            value: 1,
            storeCode: $storeFixture->getCode(),
        );

        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore($storeFixture->get());

        /** @var FlyweightFactory $themeFactory */
        $themeFactory = $this->objectManager->get(FlyweightFactory::class);
        $theme = $themeFactory->create('Magento/blank');

        $aggregated = $this->objectManager->get(AggregatedRequireJsFiles::class);
        $files = $aggregated->getFiles($theme, '*.js');

        $fileExists = false;
        foreach ($files as $file) {
            if ($file->getModule() === 'Klevu_FrontendSearch') {
                $fileExists = true;
                break;
            }
        }
        $this->assertTrue(condition: $fileExists, message: 'Frontend_Search RequireJS file exists');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAfterGetFiles_DoesNotReturnsFrontendSearchJS_WhenQuickDisabled(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');

        ConfigFixture::setForStore(
            path: Constants::XML_PATH_QUICK_ENABLED,
            value: 0,
            storeCode: $storeFixture->getCode(),
        );

        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore($storeFixture->get());

        /** @var FlyweightFactory $themeFactory */
        $themeFactory = $this->objectManager->get(FlyweightFactory::class);
        $theme = $themeFactory->create('Magento/blank');

        $aggregated = $this->objectManager->get(AggregatedRequireJsFiles::class);
        $files = $aggregated->getFiles($theme, '*.js');

        $fileExists = false;
        foreach ($files as $file) {
            if ($file->getModule() === 'Klevu_FrontendSearch') {
                $fileExists = true;
                break;
            }
        }
        $this->assertFalse(condition: $fileExists, message: 'Frontend_Search RequireJS file exists');
    }

    /**
     * @return mixed[]|null
     */
    private function getSystemConfigPluginInfo(): ?array
    {
        /** @var PluginList $pluginList */
        $pluginList = $this->objectManager->get(PluginList::class);

        return $pluginList->get(AggregatedRequireJsFiles::class, []);
    }
}
