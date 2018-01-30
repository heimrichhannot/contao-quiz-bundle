<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Frontend;

use Contao\Config;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\QuizBundle\Frontend\InsertTags;

class InsertTagsTest extends ContaoTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $router = $this->createRouterMock();
        $requestStack = $this->createRequestStackMock();
        $framework = $this->mockContaoFramework($this->createMockAdapater());

        $config = $this->createMock(Config::class);
        $database = $this->createMock(Connection::class);

        $container = $this->mockContainer();
        $container->set('request_stack', $requestStack);
        $container->set('router', $router);
        $container->set('contao.framework', $framework);
        $container->set('database_connection', $database);
        System::setContainer($container);
    }

    public function testCurrentScoreInsertTag()
    {
        $strInsertTag = '{{huh_quiz_current_score}}';

        $insertTags = new InsertTags();
        $result = $insertTags->quizInsertTags($strInsertTag);

        $this->assertSame(0, $result);
    }
}
