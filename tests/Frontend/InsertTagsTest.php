<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Frontend;


use HeimrichHannot\QuizBundle\Frontend\InsertTags;
use PHPUnit\Framework\TestCase;

class InsertTagsTest extends TestCase
{

    public function quizInsertTagsTest()
    {
        $strInsertTag = '{{huh_quiz_current_score}}';

        $insertTags = new InsertTags();
        $result     = $insertTags->quizInsertTags($strInsertTag);

        $this->assertEquals(0, $result);
    }
}