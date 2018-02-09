<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Backend;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\QuizBundle\Backend\Module;
use HeimrichHannot\QuizBundle\Model\QuizModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class ModuleTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', __DIR__);
        }

        $container = $this->mockContainer();

        $database = $this->createMock(Connection::class);
        $container->set('database_connection', $database);

        $requestStack = $this->createRequestStackMock();
        $container->set('request_stack', $requestStack);

        $router = $this->createRouterMock();
        $container->set('router', $router);

        $framework = $this->mockContaoFramework();
        $container->set('contao.framework', $framework);

        $quizModel1 = $this->mockClassWithProperties(QuizModel::class, ['id' => 1, 'title' => 'Quiz1']);
        $quizModel2 = $this->mockClassWithProperties(QuizModel::class, ['id' => 2, 'title' => 'Quiz2']);
        $quizAdapter = $this->mockAdapter(['findAll']);
        $quizAdapter->method('findAll')->willReturn([$quizModel1, $quizModel2]);
        $container->set('huh.quiz.manager', $quizAdapter);
        System::setContainer($container);
    }

    public function testGetQuizArchives()
    {
        $framework = $this->mockContaoFramework();
        $module = new Module($framework);
        $quizArchives = $module->getQuizArchives();

        $this->assertArrayHasKey(1, $quizArchives);
        $this->assertArrayHasKey(2, $quizArchives);
        $this->assertSame('Quiz1', $quizArchives[1]);
        $this->assertSame('Quiz2', $quizArchives[2]);

        $container = System::getContainer();
        $quizAdapter = $this->mockAdapter(['findAll']);
        $quizAdapter->method('findAll')->willReturn(null);
        $container->set('huh.quiz.manager', $quizAdapter);
        System::setContainer($container);

        $framework = $this->mockContaoFramework();
        $module = new Module($framework);
        $quizArchives = $module->getQuizArchives();

        $this->assertSame([], $quizArchives);
    }

    public function createRequestStackMock()
    {
        $requestStack = new RequestStack();
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);

        return $requestStack;
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
}
