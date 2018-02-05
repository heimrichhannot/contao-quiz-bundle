<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Manager;

use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\QuizBundle\Manager\QuizAnswerManager;
use HeimrichHannot\QuizBundle\Model\QuizAnswerModel;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class QuizAnswerManagerTest extends ContaoTestCase
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

        $container = $this->mockContainer();
        $container->set('kernel', $this->createMock(ContaoKernel::class));
        $container->set('session', new Session(new MockArraySessionStorage()));
        System::setContainer($container);
    }

    public function testFindOnePublishedByPid()
    {
        $quizAnswerModel = $this->mockClassWithProperties(QuizAnswerModel::class, ['id' => 1]);

        $quizAnswerAdapter = $this->mockAdapter(['findOnePublishedByPid', 'getTable', 'findOneBy']);
        $quizAnswerAdapter->method('findOnePublishedByPid')->willReturn($quizAnswerModel);
        $quizAnswerAdapter->method('getTable')->willReturn('tl_quiz_answer');
        $quizAnswerAdapter->method('findOneBy')->willReturn($quizAnswerModel);

        $framework = $this->mockContaoFramework([QuizAnswerModel::class => $quizAnswerAdapter]);

        $manager = new QuizAnswerManager($framework);

        $result = $manager->findOnePublishedByPid(1);

        $this->assertInstanceOf(QuizAnswerModel::class, $result);
    }

    public function testFindPublishedByPid()
    {
        $quizAnswerModel = $this->mockClassWithProperties(QuizAnswerModel::class, ['id' => 1]);

        $quizAnswerAdapter = $this->mockAdapter(['findPublishedByPid', 'getTable', 'findBy']);
        $quizAnswerAdapter->method('findPublishedByPid')->willReturn($quizAnswerModel);
        $quizAnswerAdapter->method('getTable')->willReturn('tl_quiz_answer');
        $quizAnswerAdapter->method('findBy')->willReturn($quizAnswerModel);

        $framework = $this->mockContaoFramework([QuizAnswerModel::class => $quizAnswerAdapter]);

        $manager = new QuizAnswerManager($framework);

        $result = $manager->findPublishedByPid(1);

        $this->assertInstanceOf(QuizAnswerModel::class, $result);
    }

    public function testFindBy()
    {
        $quizAnswerModel = $this->mockClassWithProperties(QuizAnswerModel::class, ['id' => 1]);

        $quizAnswerAdapter = $this->mockAdapter(['findBy']);
        $quizAnswerAdapter->method('findBy')->willReturn($quizAnswerModel);

        $framework = $this->mockContaoFramework([QuizAnswerModel::class => $quizAnswerAdapter]);

        $manager = new QuizAnswerManager($framework);

        $result = $manager->findBy('id', 1);

        $this->assertInstanceOf(QuizAnswerModel::class, $result);
    }

    public function testFindOneBy()
    {
        $quizAnswerModel = $this->mockClassWithProperties(QuizAnswerModel::class, ['id' => 1]);

        $quizAnswerAdapter = $this->mockAdapter(['findOneBy']);
        $quizAnswerAdapter->method('findOneBy')->willReturn($quizAnswerModel);

        $framework = $this->mockContaoFramework([QuizAnswerModel::class => $quizAnswerAdapter]);

        $manager = new QuizAnswerManager($framework);

        $result = $manager->findOneBy('id', 1);

        $this->assertInstanceOf(QuizAnswerModel::class, $result);
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
