<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Manager;

use Contao\ContentModel;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\QuizBundle\Manager\ModelManager;
use HeimrichHannot\QuizBundle\Manager\QuizEvaluationManager;
use HeimrichHannot\QuizBundle\Manager\QuizManager;
use HeimrichHannot\QuizBundle\Model\QuizEvaluationModel;
use HeimrichHannot\QuizBundle\Model\QuizModel;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Translation\Translator;

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
        $container->set('session', new Session(new MockArraySessionStorage()));

        // twig
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../src/Resources/views/');
        $loader->addPath(__DIR__.'/../../src/Resources/views/', 'HeimrichHannotContaoQuiz');
        $twig = new \Twig_Environment($loader, ['cache' => $this->getTempDir().'/var/cache/']);
        $container->set('twig', $twig);

        // translator
        $translator = new Translator('de');
        $container->set('translator', $translator);

        //secret
        $container->setParameter('secret', \Config::class);

        // model manager
        $contentModel = $this->mockClassWithProperties(ContentModel::class, ['id' => 1]);
        $contentAdapter = $this->mockAdapter(['findPublishedByPidAndTable', 'countPublishedByPidAndTable']);
        $contentAdapter->method('findPublishedByPidAndTable')->willReturn($contentModel);
        $contentAdapter->method('countPublishedByPidAndTable')->willReturn(1);
        $manager = new ModelManager($this->mockContaoFramework([ContentModel::class => $contentAdapter]));
        $container->set('huh.quiz.model.manager', $manager);

        // quiz manager
        $quizModel = $this->mockClassWithProperties(QuizModel::class, ['text' => 'text', 'title' => 'title']);
        $quizAdapter = $this->mockAdapter(['findOneBy']);
        $quizAdapter->method('findOneBy')->willReturn($quizModel);
        $manager = new QuizManager($this->mockContaoFramework([QuizModel::class => $quizAdapter]));
        $container->set('huh.quiz.manager', $manager);

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

        $result = $manager->findPublishedByPid(1, ['limit' => 1]);

        $this->assertInstanceOf(QuizEvaluationModel::class, $result);
        $this->assertSame(1, $result->id);
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
        $this->assertSame(1, $result->id);
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

        $result = $manager->findByPid(1, ['limit' => 1]);

        $this->assertInstanceOf(QuizEvaluationModel::class, $result);
        $this->assertSame(1, $result->id);
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

        $result = $manager->findOneByPid(1, ['limit' => 1]);

        $this->assertInstanceOf(QuizEvaluationModel::class, $result);
        $this->assertSame(1, $result->id);
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
        $this->assertSame(1, $result->id);
    }

    public function testParseQuizEvaluation()
    {
        $manager = new QuizEvaluationManager($this->mockContaoFramework($this->createMockAdapter()));
        $template = $manager->parseQuizEvaluation('1', 2);

        $html = '<div class="quiz-evaluation">
    <h1 class="quiz-title">title</h1>
    <div class="quiz-text">text</div>
    <div class="quiz-score">huh.quiz.answer.score</div>
    <div class="css">
    <div class="text">
        evaluationText
    </div>
        </div>
</div>';
        $this->assertSame($html, $template);

        $evalAdapter = $this->mockAdapter(['getTable', 'findBy']);
        $evalAdapter->method('getTable')->willReturn('tl_quiz_evaluation');
        $evalAdapter->method('findBy')->willReturn(null);

        $manager = new QuizEvaluationManager($this->mockContaoFramework([QuizEvaluationModel::class => $evalAdapter]));
        $template = $manager->parseQuizEvaluation('1', 2);
        $html = '<div class="quiz-evaluation">
    <h1 class="quiz-title">title</h1>
    <div class="quiz-text">text</div>
    <div class="quiz-score">huh.quiz.answer.score</div>
    
</div>';
        $this->assertSame($html, $template);
    }

    public function createMockAdapter()
    {
        $modelAdapter = $this->mockAdapter(['__construct']);

        $evalModel = $this->mockClassWithProperties(QuizEvaluationModel::class, ['imgSize' => '', 'cssClass' => 'css', 'evaluationText' => 'evaluationText']);
        $evalAdapter = $this->mockAdapter(['getTable', 'findBy']);
        $evalAdapter->method('getTable')->willReturn('tl_quiz_evaluation');
        $evalAdapter->method('findBy')->willReturn([$evalModel]);

        return [Model::class => $modelAdapter, QuizEvaluationModel::class => $evalAdapter];
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
