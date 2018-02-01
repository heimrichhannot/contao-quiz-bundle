<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Entity;

use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\QuizBundle\Entity\QuizSession;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class QuizSessionTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $container = $this->mockContainer();
        $container->set('kernel', $this->createMock(ContaoKernel::class));
        $container->set('session', new Session(new MockArraySessionStorage()));
        System::setContainer($container);
    }

    public function testSetData()
    {
        $session = new QuizSession();

        $session->setData('test', 'test');
        $data = $session->getData('test');

        $this->assertSame('test', $data);
    }

    public function testGetData()
    {
        $session = new QuizSession();

        $session->setData('test', 'test');
        $data = $session->getData('test');

        $this->assertSame('test', $data);
    }

    public function testHasData()
    {
        $session = new QuizSession();

        $session->setData('test', 'test');

        $this->assertTrue($session->hasData('test'));
    }

    public function testReset()
    {
        $session = new QuizSession();

        $session->setData('test', 'test');
        $session->reset('test');

        $this->assertFalse($session->hasData('test'));
    }

    public function testGetCurrentScore()
    {
        $session = new QuizSession();
        $this->assertSame(0, $session->getCurrentScore());
        $session->increaseScore();
        $this->assertSame(1, $session->getCurrentScore());
        $session->increaseScore();
        $this->assertSame(2, $session->getCurrentScore());
    }

    public function testIncreaseScore()
    {
        $session = new QuizSession();
        $session->increaseScore();
        $this->assertSame(1, $session->getCurrentScore());
        $session->increaseScore();
        $this->assertSame(2, $session->getCurrentScore());
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
