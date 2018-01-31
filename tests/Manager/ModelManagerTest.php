<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Choice;

use Contao\Config;
use Contao\ContentModel;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\QuizBundle\Manager\ModelManager;
use HeimrichHannot\QuizBundle\Model\QuizAnswerModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;

class ModelManagerTest extends ContaoTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $router = $this->createRouterMock();
        $requestStack = $this->createRequestStackMock();
        $framework = $this->mockContaoFramework($this->createMockAdapter());

        $database = $this->createMock(Connection::class);
        $container = $this->mockContainer();
        $container->set('kernel', $this->createMock(ContaoKernel::class));
        $container->setParameter('secret', Config::class);
        $container->set('request_stack', $requestStack);
        $container->set('router', $router);
        $container->set('session', new Session());
        $container->set('contao.framework', $framework);
        $container->set('database_connection', $database);
        System::setContainer($container);
    }

    public function testContentElementByModel()
    {
        $contentModel = $this->mockClassWithProperties(ContentModel::class, ['id' => 1]);

        $contentAdapter = $this->mockAdapter(['findPublishedByPidAndTable', 'countPublishedByPidAndTable']);
        $contentAdapter->method('findPublishedByPidAndTable')->willReturn($contentModel);
        $contentAdapter->method('countPublishedByPidAndTable')->willReturn(1);

        $framework = $this->mockContaoFramework([ContentModel::class => $contentAdapter]);

        $manager = new ModelManager($framework);

        $mockModel = $this->mockClassWithProperties(QuizAnswerModel::class, ['id' => 1, 'hasContentElement' => null, 'contentElement' => '']);

        $mockModel = $manager->getContentElementByModel($mockModel, 'tl_test');

        $this->assertInstanceOf(QuizAnswerModel::class, $mockModel);
        $this->assertSame('', $mockModel->contentElement);
        $this->assertNull($mockModel->hasContentElement);
    }

    public function createRouterMock()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->with('contao_backend', $this->anything())->will($this->returnCallback(function ($route, $params = []) {
            $url = '/contao';
            if (!empty($params)) {
                $count = 0;
                foreach ($params as $key => $value) {
                    $url .= (0 === $count ? '?' : '&');
                    $url .= $key.'='.$value;
                    ++$count;
                }
            }

            return $url;
        }));

        return $router;
    }

    public function createRequestStackMock()
    {
        $requestStack = new RequestStack();
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);

        return $requestStack;
    }

    public function createMockAdapter()
    {
        $modelAdapter = $this->mockAdapter(['__construct']);

        return [Model::class => $modelAdapter];
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
