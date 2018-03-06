<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Manager;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\QuizBundle\Manager\QuizManager;
use HeimrichHannot\QuizBundle\Model\QuizModel;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class QuizManagerTest extends ContaoTestCase
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

        $container = $this->mockContainer();
        $container->set('session', new Session(new MockArraySessionStorage()));
        System::setContainer($container);
    }

    public function testFindBy()
    {
        $quizModel = $this->mockClassWithProperties(QuizModel::class, ['id' => 1]);

        $quizManager = $this->mockAdapter(['findBy']);
        $quizManager->method('findBy')->willReturn($quizModel);

        $framework = $this->mockContaoFramework([QuizModel::class => $quizManager]);

        $manager = new QuizManager($framework);

        $result = $manager->findBy('id', 1);

        $this->assertInstanceOf(QuizModel::class, $result);
    }

    public function testFindOneBy()
    {
        $quizModel = $this->mockClassWithProperties(QuizModel::class, ['id' => 1]);

        $quizManager = $this->mockAdapter(['findOneBy']);
        $quizManager->method('findOneBy')->willReturn($quizModel);

        $framework = $this->mockContaoFramework([QuizModel::class => $quizManager]);

        $manager = new QuizManager($framework);

        $result = $manager->findOneBy('id', 1);

        $this->assertInstanceOf(QuizModel::class, $result);
    }

    public function testFindAll()
    {
        $quizModel = $this->mockClassWithProperties(QuizModel::class, ['id' => 1]);

        $quizManager = $this->mockAdapter(['findAll']);
        $quizManager->method('findAll')->willReturn($quizModel);

        $framework = $this->mockContaoFramework([QuizModel::class => $quizManager]);

        $manager = new QuizManager($framework);

        $result = $manager->findAll([]);

        $this->assertInstanceOf(QuizModel::class, $result);
    }
}
