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
use HeimrichHannot\QuizBundle\Manager\QuizQuestionManager;
use HeimrichHannot\QuizBundle\Model\QuizQuestionModel;

class QuizQuestionManagerTest extends ContaoTestCase
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
        System::setContainer($container);
    }

    public function testFindOnePublishedByPid()
    {
        $quizQuestionModel = $this->mockClassWithProperties(QuizQuestionModel::class, ['id' => 1]);

        $quizQuestionAdapter = $this->mockAdapter(['findOnePublishedByPid', 'getTable', 'findOneBy']);
        $quizQuestionAdapter->method('findOnePublishedByPid')->willReturn($quizQuestionModel);
        $quizQuestionAdapter->method('getTable')->willReturn('tl_quiz_answer');
        $quizQuestionAdapter->method('findOneBy')->willReturn($quizQuestionModel);

        $framework = $this->mockContaoFramework([QuizQuestionModel::class => $quizQuestionAdapter]);

        $manager = new QuizQuestionManager($framework);

        $result = $manager->findOnePublishedByPid(1);

        $this->assertInstanceOf(QuizQuestionModel::class, $result);
    }

    public function testFindPublishedByPid()
    {
        $quizQuestionModel = $this->mockClassWithProperties(QuizQuestionModel::class, ['id' => 1]);

        $quizQuestionAdapter = $this->mockAdapter(['findPublishedByPid', 'getTable', 'findBy']);
        $quizQuestionAdapter->method('findPublishedByPid')->willReturn($quizQuestionModel);
        $quizQuestionAdapter->method('getTable')->willReturn('tl_quiz_answer');
        $quizQuestionAdapter->method('findBy')->willReturn($quizQuestionModel);

        $framework = $this->mockContaoFramework([QuizQuestionModel::class => $quizQuestionAdapter]);

        $manager = new QuizQuestionManager($framework);

        $result = $manager->findPublishedByPid(1);

        $this->assertInstanceOf(QuizQuestionModel::class, $result);
    }

    public function testFindBy()
    {
        $quizQuestionModel = $this->mockClassWithProperties(QuizQuestionModel::class, ['id' => 1]);

        $quizQuestionAdapter = $this->mockAdapter(['findBy']);
        $quizQuestionAdapter->method('findBy')->willReturn($quizQuestionModel);

        $framework = $this->mockContaoFramework([QuizQuestionModel::class => $quizQuestionAdapter]);

        $manager = new QuizQuestionManager($framework);

        $result = $manager->findBy('id', 1);

        $this->assertInstanceOf(QuizQuestionModel::class, $result);
    }

    public function testFindOneBy()
    {
        $quizQuestionModel = $this->mockClassWithProperties(QuizQuestionModel::class, ['id' => 1]);

        $quizQuestionAdapter = $this->mockAdapter(['findOneBy']);
        $quizQuestionAdapter->method('findOneBy')->willReturn($quizQuestionModel);

        $framework = $this->mockContaoFramework([QuizQuestionModel::class => $quizQuestionAdapter]);

        $manager = new QuizQuestionManager($framework);

        $result = $manager->findOneBy('id', 1);

        $this->assertInstanceOf(QuizQuestionModel::class, $result);
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
