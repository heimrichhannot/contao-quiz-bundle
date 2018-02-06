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
use Contao\StringUtil;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\QuizBundle\Manager\ModelManager;
use HeimrichHannot\QuizBundle\Model\QuizAnswerModel;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use Monolog\Logger;
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
            \define('TL_ROOT', __DIR__);
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

        $container->set('monolog.logger.contao', new Logger('test'));

        // twig
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../src/Resources/views/');
        $loader->addPath(__DIR__.'/../../src/Resources/views/', 'HeimrichHannotContaoQuiz');
        $twig = new \Twig_Environment($loader, ['cache' => $this->getTempDir().'/var/cache/']);
        $container->set('twig', $twig);

        // utils image
        $imageAdapter = $this->mockAdapter(['addToTemplateData']);
        $imageAdapter->method('addToTemplateData')->willReturn($templateData['images']['singleSRC'] = []);
        $container->set('huh.utils.image', $imageAdapter);

        $utilsContainer = new ContainerUtil($this->mockContaoFramework());
        $container->set('huh.utils.container', $utilsContainer);

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
        $this->assertSame('', $mockModel->contentElement);
    }

    public function testAddImage()
    {
        $templateData = [];
        $mockedModel = $this->mockClassWithProperties(QuizAnswerModel::class, ['addImage' => true, 'singleSRC' => 'image']);
        $mockedModel->method('row')->willReturn(['imagemargin' => 'a:5:{s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:5:"right";s:0:"";s:3:"top";s:0:"";s:4:"unit";s:0:"";}']);
        $mockedImageModel = $this->mockClassWithProperties(FilesModel::class, ['path' => '../img/screenshot.png']);

        $filesAdapter = $this->mockAdapter(['findByUuid']);
        $filesAdapter->method('findByUuid')->willReturn($mockedImageModel);

        $stringUtilAdapter = $this->mockAdapter(['deserialize']);
        $stringUtilAdapter->method('deserialize')->willReturn([0 => 1, 1 => 2, 2 => 3]);

        $framework = $this->mockContaoFramework([FilesModel::class => $filesAdapter, StringUtil::class => $stringUtilAdapter]);
        $manager = new ModelManager($framework);

        $manager->addImage($mockedModel, $templateData, 'a:3:{i:0;s:0:"2";i:1;s:0:"2";i:2;s:0:"2";}');

        $this->assertArrayHasKey('images', $templateData);
        $this->assertArrayHasKey('singleSRC', $templateData['images']);
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
}
