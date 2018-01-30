<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Model;

/**
 * @property int    $id
 * @property string $title
 * @property int    $pid
 * @property string $solving
 * @property int    $author
 * @property bool   $addImage
 * @property string $singleSRC
 * @property string $alt
 * @property string $size
 * @property string $imagemargin
 * @property string $imageUrl
 * @property bool   $fullsize
 * @property string $caption
 * @property string $floating
 * @property string $cssClass
 * @property bool   $published
 * @property string $start
 * @property string $stop
 */
class QuizAnswerSolvingModel extends \Contao\Model
{
    protected static $strTable = 'tl_quiz_answer_solving';
}
