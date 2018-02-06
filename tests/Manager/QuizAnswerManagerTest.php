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
use Haste\Util\Url;
use HeimrichHannot\QuizBundle\Manager\ModelManager;
use HeimrichHannot\QuizBundle\Manager\QuizAnswerManager;
use HeimrichHannot\QuizBundle\Model\QuizAnswerModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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

        $framework = $this->mockContaoFramework($this->createMockAdapter());
        $container->set('contao.framework', $framework);

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
        $manager = new ModelManager($this->mockContaoFramework(array_merge($this->createMockAdapter(), [ContentModel::class => $contentAdapter])));
        $container->set('huh.quiz.model.manager', $manager);

        // request stack
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $container->set('request_stack', $requestStack);

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

    public function testParseAnswer()
    {
        $answerModel = $this->mockClassWithProperties(QuizAnswerModel::class, ['cssClass' => 'css', 'answer' => 'answer', 'imgSize' => '', 'id' => 1]);
        $manager = new QuizAnswerManager($this->mockContaoFramework($this->createMockAdapter()));
        $template = $manager->parseAnswer($answerModel);

        $html = '<div class="quiz-answer">
    <a href="https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?answer=1">
        <div class="css">
    <div class="text">
        answer
    </div>
        </div>
    </a>
</div>';
        $this->assertSame($html, $template);
    }

    public function testPrepareAnswers()
    {
        $answerModel = $this->mockClassWithProperties(QuizAnswerModel::class, ['cssClass' => 'css', 'answer' => 'answer', 'imgSize' => '', 'id' => 1]);
        $collection = new Model\Collection([$answerModel], 'tl_quiz_answer');
        $manager = new QuizAnswerManager($this->mockContaoFramework($this->createMockAdapter()));
        $result = $manager->prepareAnswers($collection);
        $html = '<div class="quiz-answer">
    <a href="https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?answer=1">
        <div class="css">
    <div class="text">
        answer
    </div>
        </div>
    </a>
</div>';
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('0', $result);
        $this->assertSame($html, $result[0]);
    }

    public function createMockAdapter()
    {
        $modelAdapter = $this->mockAdapter(['__construct']);
        $urlAdapter = $this->mockAdapter(['addQueryString', 'removeQueryString']);
        $urlAdapter->method('addQueryString')->willReturn('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8?answer=1');
        $urlAdapter->method('removeQueryString')->willReturn('https://www.anwaltauskunft.dav.hhdev/app_dev.php/rechtsquiz/arbeitsrecht/8');

        return [Model::class => $modelAdapter, Url::class => $urlAdapter];
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
