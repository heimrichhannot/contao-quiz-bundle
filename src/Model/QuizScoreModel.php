<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property string $scoreText
 * @property string $dateAdded
 * @property string $tstamp
 *
 */
class QuizScoreModel extends \Contao\Model
{
    protected static $strTable = 'tl_quiz_score';
}