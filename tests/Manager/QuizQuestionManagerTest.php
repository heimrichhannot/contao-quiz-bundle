<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Manager;

use Contao\ContentModel;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\Model\Collection;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Haste\Util\Url;
use HeimrichHannot\QuizBundle\Manager\ModelManager;
use HeimrichHannot\QuizBundle\Manager\QuizAnswerManager;
use HeimrichHannot\QuizBundle\Manager\QuizQuestionManager;
use HeimrichHannot\QuizBundle\Model\QuizAnswerModel;
use HeimrichHannot\QuizBundle\Model\QuizModel;
use HeimrichHannot\QuizBundle\Model\QuizQuestionModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Translation\Translator;

class QuizQuestionManagerTest extends ContaoTestCase
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
        $container->set('kernel', $this->createMock(ContaoKernel::class));
        $container->set('session', new Session(new MockArraySessionStorage()));

        // twig
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../src/Resources/views/');
        $loader->addPath(__DIR__.'/../../src/Resources/views/', 'HeimrichHannotContaoQuiz');
        $twig = new \Twig_Environment($loader, ['cache' => $this->getTempDir().'/var/cache/']);
        $container->set('twig', $twig);

        // model manager
        $contentModel = $this->mockClassWithProperties(ContentModel::class, ['id' => 1]);
        $contentAdapter = $this->mockAdapter(['findPublishedByPidAndTable', 'countPublishedByPidAndTable']);
        $contentAdapter->method('findPublishedByPidAndTable')->willReturn($contentModel);
        $contentAdapter->method('countPublishedByPidAndTable')->willReturn(1);
        $manager = new ModelManager($this->mockContaoFramework([ContentModel::class => $contentAdapter]));
        $container->set('huh.quiz.model.manager', $manager);

        // quiz answer manager
        $quizAnswerModel = $this->mockClassWithProperties(QuizAnswerModel::class, ['answer' => 'text', 'cssClass' => 'css', 'imgSize' => '']);
        $quizAnswerAdapter = $this->mockAdapter(['prepareAnswers', 'getTable', 'findBy']);
        $quizAnswerAdapter->method('getTable')->willReturn('tl_quiz_answer');
        $quizAnswerAdapter->method('prepareAnswers')->willReturn(['answer1', 'answer2']);
        $collection = new Collection([$quizAnswerModel], 'tl_quiz_answer');
        $quizAnswerAdapter->method('findBy')->willReturn($collection);
        $urlAdapter = $this->mockAdapter(['addQueryString', 'removeQueryString']);
        $urlAdapter->method('addQueryString')->willReturn('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?answer=1');
        $urlAdapter->method('removeQueryString')->willReturn('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8');
        $manager = new QuizAnswerManager($this->mockContaoFramework([QuizAnswerModel::class => $quizAnswerAdapter, Url::class => $urlAdapter]));
        $container->set('huh.quiz.answer.manager', $manager);

        // translator
        $translator = new Translator('de');
        $container->set('translator', $translator);

        // request stack
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $container->set('request_stack', $requestStack);

        System::setContainer($container);
    }

    public function testFindOnePublishedByPid()
    {
        $quizQuestionModel = $this->mockClassWithProperties(QuizQuestionModel::class, ['id' => 1]);

        $quizQuestionAdapter = $this->mockAdapter(['findOnePublishedByPid', 'getTable', 'findOneBy']);
        $quizQuestionAdapter->method('findOnePublishedByPid')->willReturn($quizQuestionModel);
        $quizQuestionAdapter->method('getTable')->willReturn('tl_quiz_question');
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
        $quizQuestionAdapter->method('getTable')->willReturn('tl_quiz_question');
        $quizQuestionAdapter->method('findBy')->willReturn($quizQuestionModel);

        $framework = $this->mockContaoFramework([QuizQuestionModel::class => $quizQuestionAdapter]);

        $manager = new QuizQuestionManager($framework);

        $result = $manager->findPublishedByPid(1);

        $this->assertInstanceOf(QuizQuestionModel::class, $result);
    }

    public function testFindOnePublishedByPidNotInQuestions()
    {
        $quizQuestionModel = $this->mockClassWithProperties(QuizQuestionModel::class, ['id' => 1]);
        $quizQuestionAdapter = $this->mockAdapter(['findOnePublishedByPidNotInQuestions', 'getTable', 'findOneBy']);

        $quizQuestionAdapter->method('findOnePublishedByPidNotInQuestions')->willReturn($quizQuestionModel);
        $quizQuestionAdapter->method('getTable')->willReturn('tl_quiz_question');
        $quizQuestionAdapter->method('findOneBy')->willReturn($quizQuestionModel);

        $framework = $this->mockContaoFramework([QuizQuestionModel::class => $quizQuestionAdapter]);
        $manager = new QuizQuestionManager($framework);
        $result = $manager->findOnePublishedByPidNotInQuestions(1, [2, 3], 1);

        $this->assertInstanceOf(QuizQuestionModel::class, $result);
        $this->assertSame(1, $result->id);
    }

    public function testCountByPid()
    {
        $quizQuestionAdapter = $this->mockAdapter(['countByPid', 'countBy']);

        $quizQuestionAdapter->method('countByPid')->willReturn(1);
        $quizQuestionAdapter->method('countBy')->willReturn(1);

        $framework = $this->mockContaoFramework([QuizQuestionModel::class => $quizQuestionAdapter]);
        $manager = new QuizQuestionManager($framework);
        $result = $manager->countByPid(1);

        $this->assertSame(1, $result);
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

    public function testPrepareQuestion()
    {
        $questionModel = $this->mockClassWithProperties(QuizQuestionModel::class, ['id' => 1, 'cssClass' => 'css', 'question' => 'question']);
        $quizModel = $this->mockClassWithProperties(QuizModel::class, ['id' => 1, 'text' => 'text', 'title' => 'title']);

        $manager = new QuizQuestionManager($this->mockContaoFramework());
        $template = $manager->prepareQuestion($questionModel, $quizModel, 2, '');
        $html = '    <div class="count">huh.quiz.count.text.default</div>
    <div class="quiz-question">
        <div class="css">
    <div class="text">
        question
    </div>
        </div>
    </div>
    <ul class="answer-list">
                    <li class="quiz-answer">
    <a href="https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?answer=1">
        <div class="css">
    <div class="text">
        text
    </div>
        </div>
    </a>
</li>
            </ul>
';
        $this->assertSame($html, $template);
    }
}
