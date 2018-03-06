<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Choice;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;
use HeimrichHannot\Haste\Util\Url;
use HeimrichHannot\QuizBundle\Manager\TokenManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;

class TokenManagerTest extends ContaoTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', __DIR__);
        }

        $router = $this->createRouterMock();
        $requestStack = $this->createRequestStackMock();
        $framework = $this->mockContaoFramework($this->createMockAdapter());

        $database = $this->createMock(Connection::class);
        $container = $this->mockContainer();
        $container->set('request_stack', $requestStack);
        $container->set('router', $router);
        $container->set('session', new Session(new MockArraySessionStorage()));
        $container->set('contao.framework', $framework);
        $container->set('database_connection', $database);
        $container->setParameter('secret', Config::class);
        System::setContainer($container);
    }

    public function testGetDataFromJwtToken()
    {
        $framework = $this->mockContaoFramework($this->createMockAdapter());
        $tokenManager = new TokenManager($framework);
        $encode = JWT::encode(['id' => 12], System::getContainer()->getParameter('secret'));
        $token = $tokenManager->getDataFromJwtToken($encode);

        $this->assertSame(12, $token->id);

        $framework = $this->mockContaoFramework($this->createMockAdapter());
        $tokenManager = new TokenManager($framework);
        try {
            $token = $tokenManager->getDataFromJwtToken('');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(RedirectResponseException::class, $exception);
            $this->assertSame('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzZXNzaW9uIjoic2pja3IwZGxvNGJqZm1wZmRpb2hubGZwcWkiLCIxMSI6IjIyIn0.8O6LzSHEk3A-TQ3PRsBuW4TkQasFpzDeM08YO2FKEpE&answer=21', $exception->getResponse()->getTargetUrl());
        }
    }

    public function testAddDataToJwtToken()
    {
        $framework = $this->mockContaoFramework($this->createMockAdapter());
        $tokenManager = new TokenManager($framework);
        $encode = JWT::encode(['session' => ''], System::getContainer()->getParameter('secret'));
        $token = $tokenManager->addDataToJwtToken($encode, 12, 'id');
        $decoded = JWT::decode($token, System::getContainer()->getParameter('secret'), ['HS256']);

        $this->assertSame('', $decoded->session);
        $this->assertSame(12, $decoded->id);

        $tokenManager = new TokenManager($framework);
        try {
            $token = $tokenManager->addDataToJwtToken('', 12, 'id');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(RedirectResponseException::class, $exception);
            $this->assertSame('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzZXNzaW9uIjoic2pja3IwZGxvNGJqZm1wZmRpb2hubGZwcWkiLCIxMSI6IjIyIn0.8O6LzSHEk3A-TQ3PRsBuW4TkQasFpzDeM08YO2FKEpE&answer=21', $exception->getResponse()->getTargetUrl());
        }

        $encode = JWT::encode(['session' => '123456789'], System::getContainer()->getParameter('secret'));
        $tokenManager = new TokenManager($framework);
        try {
            $token = $tokenManager->addDataToJwtToken($encode, 12, 'id');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(RedirectResponseException::class, $exception);
            $this->assertSame('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzZXNzaW9uIjoic2pja3IwZGxvNGJqZm1wZmRpb2hubGZwcWkiLCIxMSI6IjIyIn0.8O6LzSHEk3A-TQ3PRsBuW4TkQasFpzDeM08YO2FKEpE&answer=21', $exception->getResponse()->getTargetUrl());
        }
    }

    public function testGetCurrentScore()
    {
        $framework = $this->mockContaoFramework($this->createMockAdapter());
        $tokenManager = new TokenManager($framework);
        $encode = JWT::encode(['session' => ''], System::getContainer()->getParameter('secret'));
        $token = $tokenManager->increaseScore($encode, 1);
        $score = $tokenManager->getCurrentScore($token);
        $this->assertSame(1, $score);
        $token = $tokenManager->increaseScore($token, 1);
        $score = $tokenManager->getCurrentScore($token);
        $this->assertSame(2, $score);
    }

    public function testIncreaseScore()
    {
        $framework = $this->mockContaoFramework($this->createMockAdapter());
        $tokenManager = new TokenManager($framework);
        $encode = JWT::encode(['session' => ''], System::getContainer()->getParameter('secret'));
        $token = $tokenManager->increaseScore($encode, 1);
        $decoded = JWT::decode($token, System::getContainer()->getParameter('secret'), ['HS256']);

        $this->assertSame(1, $decoded->score);
        $token = $tokenManager->increaseScore($token, 1);
        $decoded = JWT::decode($token, System::getContainer()->getParameter('secret'), ['HS256']);
        $this->assertSame(2, $decoded->score);

        $tokenManager = new TokenManager($framework);
        try {
            $token = $tokenManager->increaseScore('', 1);
        } catch (\Exception $exception) {
            $this->assertInstanceOf(RedirectResponseException::class, $exception);
            $this->assertSame('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzZXNzaW9uIjoic2pja3IwZGxvNGJqZm1wZmRpb2hubGZwcWkiLCIxMSI6IjIyIn0.8O6LzSHEk3A-TQ3PRsBuW4TkQasFpzDeM08YO2FKEpE&answer=21', $exception->getResponse()->getTargetUrl());
        }

        $encode = JWT::encode(['session' => '123456789'], System::getContainer()->getParameter('secret'));
        $tokenManager = new TokenManager($framework);
        try {
            $token = $tokenManager->increaseScore($encode, 1);
        } catch (\Exception $exception) {
            $this->assertInstanceOf(RedirectResponseException::class, $exception);
            $this->assertSame('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzZXNzaW9uIjoic2pja3IwZGxvNGJqZm1wZmRpb2hubGZwcWkiLCIxMSI6IjIyIn0.8O6LzSHEk3A-TQ3PRsBuW4TkQasFpzDeM08YO2FKEpE&answer=21', $exception->getResponse()->getTargetUrl());
        }
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
        $urlAdapter = $this->mockAdapter(['addQueryString']);
        $urlAdapter->method('addQueryString')->willReturn('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzZXNzaW9uIjoic2pja3IwZGxvNGJqZm1wZmRpb2hubGZwcWkiLCIxMSI6IjIyIn0.8O6LzSHEk3A-TQ3PRsBuW4TkQasFpzDeM08YO2FKEpE&answer=21');
        $controllerAdapter = $this->mockAdapter(['redirect']);
        $controllerAdapter->method('redirect')->willThrowException(new RedirectResponseException('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzZXNzaW9uIjoic2pja3IwZGxvNGJqZm1wZmRpb2hubGZwcWkiLCIxMSI6IjIyIn0.8O6LzSHEk3A-TQ3PRsBuW4TkQasFpzDeM08YO2FKEpE&answer=21'));

        return [Model::class => $modelAdapter, Url::class => $urlAdapter, Controller::class => $controllerAdapter];
    }
}
