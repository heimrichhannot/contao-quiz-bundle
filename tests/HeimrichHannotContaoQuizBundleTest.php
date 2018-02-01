<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test;

use HeimrichHannot\QuizBundle\DependencyInjection\HeimrichHannotContaoQuizExtension;
use HeimrichHannot\QuizBundle\HeimrichHannotContaoQuizBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoQuizBundleTest extends TestCase
{
    public function testGetContainerExtension()
    {
        $bundle = new HeimrichHannotContaoQuizBundle();

        $this->assertInstanceOf(HeimrichHannotContaoQuizExtension::class, $bundle->getContainerExtension());
    }
}
