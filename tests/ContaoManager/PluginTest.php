<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\PluginLoader;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle;
use HeimrichHannot\QuizBundle\ContaoManager\Plugin;
use HeimrichHannot\QuizBundle\HeimrichHannotContaoQuizBundle;

/**
 * Test the plugin class
 * Class PluginTest.
 */
class PluginTest extends ContaoTestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->container = new ContainerBuilder($this->mockPluginLoader($this->never()), []);
    }

    public function testInstantiation()
    {
        static::assertInstanceOf(Plugin::class, new Plugin());
    }

    public function testGetBundles()
    {
        $plugin = new Plugin();

        /** @var BundleConfig[] $bundles */
        $bundles = $plugin->getBundles(new DelegatingParser());

        static::assertCount(1, $bundles);
        static::assertInstanceOf(BundleConfig::class, $bundles[0]);
        static::assertEquals(HeimrichHannotContaoQuizBundle::class, $bundles[0]->getName());
        static::assertEquals([ContaoCoreBundle::class, 'submissions_creator', 'submissions', HeimrichHannotContaoListBundle::class], $bundles[0]->getLoadAfter());
    }

    /**
     * Test extend configuration.
     */
    public function testGetExtensionConfig()
    {
        $plugin = new Plugin();
        $extensionConfigs = $plugin->getExtensionConfig('huh_list', [[]], $this->container);
        $this->assertNotEmpty($extensionConfigs);
        $this->assertArrayHasKey('huh', $extensionConfigs);
        $this->assertArrayHasKey('list', $extensionConfigs['huh']);
        $this->assertArrayHasKey('templates', $extensionConfigs['huh']['list']);
        $this->assertArrayHasKey('item', $extensionConfigs['huh']['list']['templates']);
        $this->assertArrayHasKey('0', $extensionConfigs['huh']['list']['templates']['item']);
        $this->assertArrayHasKey('name', $extensionConfigs['huh']['list']['templates']['item']['0']);
        $this->assertSame('quiz-list-item', $extensionConfigs['huh']['list']['templates']['item']['0']['name']);
        $this->assertArrayHasKey('template', $extensionConfigs['huh']['list']['templates']['item']['0']);
        $this->assertSame('@HeimrichHannotContaoQuiz/item/list_item_quiz_default.html.twig', $extensionConfigs['huh']['list']['templates']['item']['0']['template']);
    }

    /**
     * Mocks the plugin loader.
     *
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $expects
     * @param array                                                 $plugins
     *
     * @return PluginLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPluginLoader(\PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $expects, array $plugins = [])
    {
        $pluginLoader = $this->createMock(PluginLoader::class);
        $pluginLoader->expects($expects)->method('getInstancesOf')->with(PluginLoader::EXTENSION_PLUGINS)->willReturn($plugins);

        return $pluginLoader;
    }
}
