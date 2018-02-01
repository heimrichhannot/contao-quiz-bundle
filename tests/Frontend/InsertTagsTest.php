<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Frontend;

use Contao\Controller;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\Model;
use Contao\ModuleModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\QuizBundle\Frontend\InsertTags;
use HeimrichHannot\QuizBundle\Manager\QuizQuestionManager;
use HeimrichHannot\QuizBundle\Model\QuizQuestionModel;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class InsertTagsTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $GLOBALS['FE_MOD']['quiz'] = [
            'quiz' => 'HeimrichHannot\QuizBundle\Module\ModuleQuizReader',
            'quizSubmission' => 'HeimrichHannot\QuizBundle\Module\ModuleQuizSubmission',
        ];

        if (!defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        if (!defined('TL_ERROR')) {
            \define('TL_ERROR', 'ERROR');
        }

        $quizQuestionManager = new QuizQuestionManager($this->mockContaoFramework([QuizQuestionModel::class => $this->getQuizQuestionAdapter()]));

        $database = $this->createMock(Connection::class);
        $container = $this->mockContainer();
        $container->set('kernel', $this->createMock(ContaoKernel::class));
        $container->set('session', new Session(new MockArraySessionStorage()));
        $container->set('database_connection', $database);
        $container->set('huh.quiz.question.manager', $quizQuestionManager);
        $container->set('monolog.logger.contao', new Logger('test'));
        $container->set('contao.framework', $this->mockContaoFramework($this->createMockAdapter()));
        System::setContainer($container);
    }

    public function testQuizInsertTags()
    {
        $strTotalScore = InsertTags::TOTAL_SCORE.'::1';

        $insertTag = new InsertTags();

        $resultCurrentScore = $insertTag->quizInsertTags(InsertTags::CURRENT_SCORE);
        $resultTotalScore = $insertTag->quizInsertTags($strTotalScore);
        $resultFalse = $insertTag->quizInsertTags('bla');
        $resultQuiz = $insertTag->quizInsertTags(InsertTags::QUIZ);

        $this->assertSame(0, $resultCurrentScore);
        $this->assertSame(2, $resultTotalScore);
        $this->assertFalse($resultFalse);
        $this->assertSame('', $resultQuiz);
    }

    public function testGetQuiz()
    {
        $insertTag = new InsertTags();

        $quiz = $insertTag->getQuiz(1, 2);
        $this->assertSame('', $quiz);
    }

    public function createMockAdapter()
    {
        $modelAdapter = $this->mockAdapter(['__construct']);

        return [Model::class => $modelAdapter, ModuleModel::class => $this->getModuleAdapter(), Controller::class => $this->getControllerAdapter()];
    }

    public function getModuleAdapter()
    {
        $moduleMock = $this->mockClassWithProperties(ModuleModel::class, ['id' => 1]);
        $moduleAdapter = $this->mockAdapter(['findByIdOrAlias']);
        $moduleAdapter->method('findByIdOrAlias')->willReturn($moduleMock);

        return $moduleAdapter;
    }

    public function getControllerAdapter()
    {
        $controllerAdapter = $this->mockAdapter(['getFrontendModule']);
        $controllerAdapter->method('getFrontendModule')->willReturn('');

        return $controllerAdapter;
    }

    public function getQuizQuestionAdapter()
    {
        $quizQuestionAdapter = $this->mockAdapter(['countByPid', 'countBy']);
        $quizQuestionAdapter->method('countByPid')->willReturn(2);
        $quizQuestionAdapter->method('countBy')->willReturn(2);

        return $quizQuestionAdapter;
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
