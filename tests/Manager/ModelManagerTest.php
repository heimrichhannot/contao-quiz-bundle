<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Choice;

use Contao\Config;
use Contao\ContentModel;
use Contao\FilesModel;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\Model;
use Contao\Module;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\QuizBundle\Manager\ModelManager;
use HeimrichHannot\QuizBundle\Model\QuizAnswerModel;
use HeimrichHannot\UtilsBundle\Image\Image;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;

class ModelManagerTest extends ContaoTestCase
{
    public static function tearDownAfterClass(): void
    {
        // The temporary directory would not be removed without this call!
        parent::tearDownAfterClass();
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->mkdir($this->getTempDir().'/var/cache/');

        $container = $this->mockContainer();

        $router = $this->createRouterMock();
        $container->set('router', $router);

        $requestStack = $this->createRequestStackMock();
        $container->set('request_stack', $requestStack);

        $framework = $this->mockContaoFramework($this->createMockAdapter());
        $container->set('contao.framework', $framework);

        $database = $this->createMock(Connection::class);
        $container->set('database_connection', $database);

        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../src/Resources/views/');
        $loader->addPath(__DIR__.'/../../src/Resources/views/', 'HeimrichHannotContaoQuiz');
        $twig = new \Twig_Environment($loader, ['cache' => $this->getTempDir().'/var/cache/']);
        $container->set('twig', $twig);

        $imageAdapter = $this->mockAdapter(['addToTemplateData']);
        $imageAdapter->method('addToTemplateData')->willReturn($templateData['images']['singleSRC'] = []);
        $container->set('huh.utils.image', $this->mockContaoFramework([Image::class => $imageAdapter]));

        $container->setParameter('secret', Config::class);
        $container->set('kernel', $this->createMock(ContaoKernel::class));
        $container->set('session', new Session(new MockArraySessionStorage()));
        System::setContainer($container);
    }

    public function testGetContentElementByModel()
    {
        $contentModel = $this->mockClassWithProperties(ContentModel::class, []);
        $contentAdapter = $this->mockAdapter(['findPublishedByPidAndTable', 'countPublishedByPidAndTable']);
        $moduleAdapter = $this->mockAdapter(['getContentElement']);

        $contentAdapter->method('findPublishedByPidAndTable')->willReturn([$contentModel]);
        $contentAdapter->method('countPublishedByPidAndTable')->willReturn(1);
        $moduleAdapter->method('getContentElement')->willReturn('Template');

        $framework = $this->mockContaoFramework([ContentModel::class => $contentAdapter, Module::class => $moduleAdapter]);
        $mockModel = $this->mockClassWithProperties(QuizAnswerModel::class, ['id' => 1, 'contentElement' => '', 'hasContentElement' => false]);

        $manager = new ModelManager($framework);
        $mockModel = $manager->getContentElementByModel($mockModel, 'tl_test');

        $this->assertInstanceOf(QuizAnswerModel::class, $mockModel);
        $this->assertFalse($mockModel->hasContentElement);
        $this->assertSame('Template', $mockModel->contentElement);
    }

    public function testAddImage()
    {
        $templateData = [];
        $mockedModel = $this->mockClassWithProperties(QuizAnswerModel::class, ['addImage' => true, 'singleSRC' => 'image']);
        $mockedModel->method('row')->willReturn([]);
        $mockedImageModel = $this->mockClassWithProperties(FilesModel::class, ['path' => '../../../docs/screenshot-add-answer.png']);

        $filesAdapter = $this->mockAdapter(['findByUuid']);
        $filesAdapter->method('findByUuid')->willReturn($mockedImageModel);

        $framework = $this->mockContaoFramework([FilesModel::class => $filesAdapter]);
        $manager = new ModelManager($framework);

        $manager->addImage($mockedModel, $templateData, '');

        $this->assertSame([], $templateData);
    }

    public function testParseModel()
    {
        $contentModel = $this->mockClassWithProperties(ContentModel::class, ['id' => 1]);

        $contentAdapter = $this->mockAdapter(['findPublishedByPidAndTable', 'countPublishedByPidAndTable']);
        $contentAdapter->method('findPublishedByPidAndTable')->willReturn($contentModel);
        $contentAdapter->method('countPublishedByPidAndTable')->willReturn(1);

        $mockedModel = $this->mockClassWithProperties(QuizAnswerModel::class, []);
        $manager = new ModelManager($this->mockContaoFramework(array_merge($this->createMockAdapter(), [ContentModel::class => $contentAdapter])));
        $result = $manager->parseModel($mockedModel, 'Das ist ein Text.', 'tl_quiz_answer', '', '');

        $html = '<div class="">
    <div class="text">
        Das ist ein Text.
    </div>
        </div>';
        $this->assertSame($html, $result);
    }

    public function createRouterMock()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->with('contao_backend', $this->anything())->will($this->returnCallback(function ($route, $params = []) {
            $url = '/contao';
            if (!empty($params)) {
                $count = 0;
                foreach ($params as $key => $value) {
                    $url .= (0 === $count ? '?' : '&');
                    $url .= $key.'='.$value;
                    ++$count;
                }
            }

            return $url;
        }));

        return $router;
    }

    public function createRequestStackMock()
    {
        $requestStack = new RequestStack();
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);

        return $requestStack;
    }

    public function createMockAdapter()
    {
        $modelAdapter = $this->mockAdapter(['__construct']);

        return [Model::class => $modelAdapter];
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
