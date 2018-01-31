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
use HeimrichHannot\QuizBundle\Manager\QuizManager;
use HeimrichHannot\QuizBundle\Model\QuizModel;

class QuizManagerTest extends ContaoTestCase
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

    public function testFindBy()
    {
        $quizModel = $this->mockClassWithProperties(QuizModel::class, ['id' => 1]);

        $quizManager = $this->mockAdapter(['findBy']);
        $quizManager->method('findBy')->willReturn($quizModel);

        $framework = $this->mockContaoFramework([QuizModel::class => $quizManager]);

        $manager = new QuizManager($framework);

        $result = $manager->findBy('id', 1);

        $this->assertInstanceOf(QuizModel::class, $result);
    }

    public function testFindOneBy()
    {
        $quizModel = $this->mockClassWithProperties(QuizModel::class, ['id' => 1]);

        $quizManager = $this->mockAdapter(['findOneBy']);
        $quizManager->method('findOneBy')->willReturn($quizModel);

        $framework = $this->mockContaoFramework([QuizModel::class => $quizManager]);

        $manager = new QuizManager($framework);

        $result = $manager->findOneBy('id', 1);

        $this->assertInstanceOf(QuizModel::class, $result);
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
