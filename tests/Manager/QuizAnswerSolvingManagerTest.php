<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Manager;

use Contao\Config;
use Contao\ContentModel;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Firebase\JWT\JWT;
use Haste\Util\Url;
use HeimrichHannot\QuizBundle\Manager\ModelManager;
use HeimrichHannot\QuizBundle\Manager\QuizAnswerManager;
use HeimrichHannot\QuizBundle\Manager\QuizAnswerSolvingManager;
use HeimrichHannot\QuizBundle\Manager\QuizQuestionManager;
use HeimrichHannot\QuizBundle\Manager\TokenManager;
use HeimrichHannot\QuizBundle\Model\QuizAnswerModel;
use HeimrichHannot\QuizBundle\Model\QuizAnswerSolvingModel;
use HeimrichHannot\QuizBundle\Model\QuizQuestionModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Translation\Translator;

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

        // quiz question manager
        $quizQuestionModel = $this->mockClassWithProperties(QuizQuestionModel::class, ['id' => 1]);
        $quizQuestionAdapter = $this->mockAdapter(['findOnePublishedByPidNotInQuestions', 'getTable', 'findOneBy']);

        $quizQuestionAdapter->method('findOnePublishedByPidNotInQuestions')->willReturn($quizQuestionModel);
        $quizQuestionAdapter->method('getTable')->willReturn('tl_quiz_question');
        $quizQuestionAdapter->method('findOneBy')->willReturn($quizQuestionModel);

        $framework = $this->mockContaoFramework([QuizQuestionModel::class => $quizQuestionAdapter]);
        $manager = new QuizQuestionManager($framework);
        $container->set('huh.quiz.question.manager', $manager);

        // quiz answer manager
        $quizAnswerModel = $this->mockClassWithProperties(QuizQuestionModel::class, ['id' => 1, 'pid' => 1, 'isSolution' => true]);
        $quizAnswerAdapter = $this->mockAdapter(['findBy']);
        $quizAnswerAdapter->method('findBy')->willReturn($quizAnswerModel);

        $framework = $this->mockContaoFramework([QuizAnswerModel::class => $quizAnswerAdapter]);
        $manager = new QuizAnswerManager($framework);
        $container->set('huh.quiz.answer.manager', $manager);

        // translator
        $translator = new Translator('de');
        $container->set('translator', $translator);
        $container->setParameter('secret', Config::class);

        // request stack
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $container->set('request_stack', $requestStack);

        // twig
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../src/Resources/views/');
        $loader->addPath(__DIR__.'/../../src/Resources/views/', 'HeimrichHannotContaoQuiz');
        $twig = new \Twig_Environment($loader, ['cache' => $this->getTempDir().'/var/cache/']);
        $container->set('twig', $twig);

        // token manager
        $framework = $this->mockContaoFramework($this->createMockAdapter());
        $tokenManager = new TokenManager($framework);
        $container->set('huh.quiz.token.manager', $tokenManager);

        // model manager
        $contentModel = $this->mockClassWithProperties(ContentModel::class, ['id' => 1]);
        $contentAdapter = $this->mockAdapter(['findPublishedByPidAndTable', 'countPublishedByPidAndTable']);
        $contentAdapter->method('findPublishedByPidAndTable')->willReturn($contentModel);
        $contentAdapter->method('countPublishedByPidAndTable')->willReturn(1);
        $manager = new ModelManager($this->mockContaoFramework(array_merge($this->createMockAdapter(), [ContentModel::class => $contentAdapter])));
        $container->set('huh.quiz.model.manager', $manager);

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

    public function testParseAnswerSolving()
    {
        $token = JWT::encode(['session' => System::getContainer()->get('session')->getId()], System::getContainer()->getParameter('secret'));
        $manager = new QuizAnswerSolvingManager($this->mockContaoFramework($this->createMockAdapter()));
        $template = $manager->parseAnswerSolving('1', '1', $token);

        $html = '<div class="quiz-answer-solving">
    huh.quiz.answer.solving.correct<div class="css">
    <div class="text">
        correct
    </div>
        </div>

    <a href="https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?answer=1">huh.quiz.answer.solving.next</a>
</div>';
        $this->assertSame($html, $template);

        $container = System::getContainer();
        // quiz answer manager
        $quizAnswerAdapter = $this->mockAdapter(['findBy']);
        $quizAnswerAdapter->method('findBy')->willReturn(null);

        $framework = $this->mockContaoFramework([QuizAnswerModel::class => $quizAnswerAdapter]);
        $manager = new QuizAnswerManager($framework);
        $container->set('huh.quiz.answer.manager', $manager);

        $token = JWT::encode(['session' => System::getContainer()->get('session')->getId()], System::getContainer()->getParameter('secret'));
        $manager = new QuizAnswerSolvingManager($this->mockContaoFramework($this->createMockAdapter()));
        $template = $manager->parseAnswerSolving('1', '1', $token);

        $this->assertSame('huh.quiz.answer.error', $template);
    }

    public function testGetNexQuestionUrl()
    {
        $manager = new QuizAnswerSolvingManager($this->mockContaoFramework($this->createMockAdapter()));
        $templateData = $manager->getNextQuestionUrl('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzZXNzaW9uIjoic2pja3IwZGxvNGJqZm1wZmRpb2hubGZwcWkiLCIxMSI6IjIyIn0.8O6LzSHEk3A-TQ3PRsBuW4TkQasFpzDeM08YO2FKEpE', '1');

        $this->assertCount(2, $templateData);
        $this->assertArrayHasKey('linkText', $templateData);
        $this->assertArrayHasKey('href', $templateData);

        $quizQuestionAdapter = $this->mockAdapter(['findOnePublishedByPidNotInQuestions', 'getTable', 'findOneBy']);
        $quizQuestionAdapter->method('findOnePublishedByPidNotInQuestions')->willReturn(null);
        $quizQuestionAdapter->method('getTable')->willReturn('tl_quiz_question');
        $quizQuestionAdapter->method('findOneBy')->willReturn(null);

        $container = System::getContainer();
        $framework = $this->mockContaoFramework([QuizQuestionModel::class => $quizQuestionAdapter]);
        $manager = new QuizQuestionManager($framework);
        $container->set('huh.quiz.question.manager', $manager);

        $token = JWT::encode(['session' => System::getContainer()->get('session')->getId()], System::getContainer()->getParameter('secret'));
        $manager = new QuizAnswerSolvingManager($this->mockContaoFramework($this->createMockAdapter()));
        $templateData = $manager->getNextQuestionUrl($token, '1');

        $this->assertCount(2, $templateData);
        $this->assertArrayHasKey('linkText', $templateData);
        $this->assertArrayHasKey('href', $templateData);
    }

    public function createMockAdapter()
    {
        $modelAdapter = $this->mockAdapter(['__construct']);

        $urlAdapter = $this->mockAdapter(['addQueryString', 'removeQueryString']);
        $urlAdapter->method('addQueryString')->willReturn('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?answer=1');
        $urlAdapter->method('removeQueryString')->willReturn('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8');

        $quizAnswerSolvingModel = $this->mockClassWithProperties(QuizAnswerSolvingModel::class, ['id' => 1, 'solving' => 'correct', 'cssClass' => 'css', 'imgSize' => '']);
        $quizAnswerSolvingAdapter = $this->mockAdapter(['getTable', 'findBy']);
        $quizAnswerSolvingAdapter->method('getTable')->willReturn('tl_quiz_answer_solving');
        $quizAnswerSolvingAdapter->method('findBy')->willReturn([$quizAnswerSolvingModel]);

        return [Model::class => $modelAdapter, Url::class => $urlAdapter, QuizAnswerSolvingModel::class => $quizAnswerSolvingAdapter];
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
