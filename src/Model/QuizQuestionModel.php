<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Model;

/**
 * @property int     $id
 * @property string  $title
 * @property integer $pid
 * @property string  question
 * @property integer $author
 * @property boolean $addImage
 * @property string  $singleSRC
 * @property string  $alt
 * @property string  $size
 * @property string  $imagemargin
 * @property string  $imageUrl
 * @property boolean $fullsize
 * @property string  $caption
 * @property string  $floating
 * @property string  $cssClass
 * @property boolean $published
 * @property string  $start
 * @property string  $stop
 */
class QuizQuestionModel extends \Contao\Model
{
    protected static $strTable = 'tl_quiz_question';
}