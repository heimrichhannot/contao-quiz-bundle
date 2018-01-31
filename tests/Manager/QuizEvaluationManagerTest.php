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
use HeimrichHannot\QuizBundle\Manager\QuizEvaluationManager;
use HeimrichHannot\QuizBundle\Model\QuizEvaluationModel;

class QuizEvaluationManagerTest extends ContaoTestCase
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

    public function testFindPublishedByPid()
    {
        $quizEvaluationModel = $this->mockClassWithProperties(QuizEvaluationModel::class, ['id' => 1]);

        $quizEvaluationAdapter = $this->mockAdapter(['findPublishedByPid', 'getTable', 'findBy']);
        $quizEvaluationAdapter->method('findPublishedByPid')->willReturn($quizEvaluationModel);
        $quizEvaluationAdapter->method('getTable')->willReturn('tl_quiz_evaluation');
        $quizEvaluationAdapter->method('findBy')->willReturn($quizEvaluationModel);

        $framework = $this->mockContaoFramework([QuizEvaluationModel::class => $quizEvaluationAdapter]);

        $manager = new QuizEvaluationManager($framework);

        $result = $manager->findPublishedByPid(1);

        $this->assertInstanceOf(QuizEvaluationModel::class, $result);
    }

    public function testFindBy()
    {
        $quizEvaluationModel = $this->mockClassWithProperties(QuizEvaluationModel::class, ['id' => 1]);

        $quizEvaluationAdapter = $this->mockAdapter(['findBy']);
        $quizEvaluationAdapter->method('findBy')->willReturn($quizEvaluationModel);

        $framework = $this->mockContaoFramework([QuizEvaluationModel::class => $quizEvaluationAdapter]);

        $manager = new QuizEvaluationManager($framework);

        $result = $manager->findBy('id', 1);

        $this->assertInstanceOf(QuizEvaluationModel::class, $result);
    }

    public function testFindByPid()
    {
        $quizEvaluationModel = $this->mockClassWithProperties(QuizEvaluationModel::class, ['id' => 1]);

        $quizEvaluationAdapter = $this->mockAdapter(['findByPid', 'getTable', 'findBy']);
        $quizEvaluationAdapter->method('findByPid')->willReturn($quizEvaluationModel);
        $quizEvaluationAdapter->method('getTable')->willReturn('tl_quiz_evaluation');
        $quizEvaluationAdapter->method('findBy')->willReturn($quizEvaluationModel);

        $framework = $this->mockContaoFramework([QuizEvaluationModel::class => $quizEvaluationAdapter]);

        $manager = new QuizEvaluationManager($framework);

        $result = $manager->findByPid(1);

        $this->assertInstanceOf(QuizEvaluationModel::class, $result);
    }

    public function testFindOneByPid()
    {
        $quizEvaluationModel = $this->mockClassWithProperties(QuizEvaluationModel::class, ['id' => 1]);

        $quizEvaluationAdapter = $this->mockAdapter(['findOneByPid', 'getTable', 'findOneBy']);
        $quizEvaluationAdapter->method('findOneByPid')->willReturn($quizEvaluationModel);
        $quizEvaluationAdapter->method('getTable')->willReturn('tl_quiz_evaluation');
        $quizEvaluationAdapter->method('findOneBy')->willReturn($quizEvaluationModel);

        $framework = $this->mockContaoFramework([QuizEvaluationModel::class => $quizEvaluationAdapter]);

        $manager = new QuizEvaluationManager($framework);

        $result = $manager->findOneByPid(1);

        $this->assertInstanceOf(QuizEvaluationModel::class, $result);
    }

    public function testFindOneBy()
    {
        $quizEvaluationModel = $this->mockClassWithProperties(QuizEvaluationModel::class, ['id' => 1]);

        $quizEvaluationAdapter = $this->mockAdapter(['findOneBy']);
        $quizEvaluationAdapter->method('findOneBy')->willReturn($quizEvaluationModel);

        $framework = $this->mockContaoFramework([QuizEvaluationModel::class => $quizEvaluationAdapter]);

        $manager = new QuizEvaluationManager($framework);

        $result = $manager->findOneBy('id', 1);

        $this->assertInstanceOf(QuizEvaluationModel::class, $result);
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
