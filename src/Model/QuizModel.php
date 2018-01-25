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
 * @property string  $cssClass
 * @property boolean $published
 * @property string  $start
 * @property string  $stop
 * @property int     $addSubmission
 * @property int     $submissionArchive
 *
 */
class QuizModel extends \Contao\Model
{
    protected static $strTable = 'tl_quiz';
}