<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Backend;

use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class ContentTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', __DIR__);
        }

        $container = $this->mockContainer();
        $container->set('kernel', $this->createMock(ContaoKernel::class));

        $requestStack = $this->createRequestStackMock();
        $container->set('request_stack', $requestStack);

        $router = $this->createRouterMock();
        $container->set('router', $router);

        $database = $this->createMock(Connection::class);
        $container->set('database_connection', $database);
        System::setContainer($container);
    }

    public function testCheckPermission()
    {
        $content = new \HeimrichHannot\QuizBundle\Backend\Content();
        $result = $content->checkPermission('tl_quiz_question', 'tl_quiz');
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
