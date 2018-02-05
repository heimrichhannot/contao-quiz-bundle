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
use HeimrichHannot\QuizBundle\Manager\QuizAnswerSolvingManager;
use HeimrichHannot\QuizBundle\Model\QuizAnswerSolvingModel;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class QuizAnswerSolvingManagerTest extends ContaoTestCase
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
        $quizAnswerModel = $this->mockClassWithProperties(QuizAnswerSolvingModel::class, ['id' => 1]);

        $quizAnswerSolvingAdapter = $this->mockAdapter(['findOnePublishedByPid', 'getTable', 'findOneBy']);
        $quizAnswerSolvingAdapter->method('findOnePublishedByPid')->willReturn($quizAnswerModel);
        $quizAnswerSolvingAdapter->method('getTable')->willReturn('tl_quiz_answer');
        $quizAnswerSolvingAdapter->method('findOneBy')->willReturn($quizAnswerModel);

        $framework = $this->mockContaoFramework([QuizAnswerSolvingModel::class => $quizAnswerSolvingAdapter]);

        $manager = new QuizAnswerSolvingManager($framework);

        $result = $manager->findOnePublishedByPid(1);

        $this->assertInstanceOf(QuizAnswerSolvingModel::class, $result);
    }

    public function testFindPublishedByPid()
    {
        $quizAnswerModel = $this->mockClassWithProperties(QuizAnswerSolvingModel::class, ['id' => 1]);

        $quizAnswerSolvingAdapter = $this->mockAdapter(['findPublishedByPid', 'getTable', 'findBy']);
        $quizAnswerSolvingAdapter->method('findPublishedByPid')->willReturn($quizAnswerModel);
        $quizAnswerSolvingAdapter->method('getTable')->willReturn('tl_quiz_answer');
        $quizAnswerSolvingAdapter->method('findBy')->willReturn($quizAnswerModel);

        $framework = $this->mockContaoFramework([QuizAnswerSolvingModel::class => $quizAnswerSolvingAdapter]);

        $manager = new QuizAnswerSolvingManager($framework);

        $result = $manager->findPublishedByPid(1);

        $this->assertInstanceOf(QuizAnswerSolvingModel::class, $result);
    }

    public function testFindBy()
    {
        $quizAnswerSolvingModel = $this->mockClassWithProperties(QuizAnswerSolvingModel::class, ['id' => 1]);

        $quizAnswerSolvingAdapter = $this->mockAdapter(['findBy']);
        $quizAnswerSolvingAdapter->method('findBy')->willReturn($quizAnswerSolvingModel);

        $framework = $this->mockContaoFramework([QuizAnswerSolvingModel::class => $quizAnswerSolvingAdapter]);

        $manager = new QuizAnswerSolvingManager($framework);

        $result = $manager->findBy('id', 1);

        $this->assertInstanceOf(QuizAnswerSolvingModel::class, $result);
    }

    public function testFindOneBy()
    {
        $quizAnswerSolvingModel = $this->mockClassWithProperties(QuizAnswerSolvingModel::class, ['id' => 1]);

        $quizAnswerSolvingAdapter = $this->mockAdapter(['findOneBy']);
        $quizAnswerSolvingAdapter->method('findOneBy')->willReturn($quizAnswerSolvingModel);

        $framework = $this->mockContaoFramework([QuizAnswerSolvingModel::class => $quizAnswerSolvingAdapter]);

        $manager = new QuizAnswerSolvingManager($framework);

        $result = $manager->findOneBy('id', 1);

        $this->assertInstanceOf(QuizAnswerSolvingModel::class, $result);
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
