<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Frontend;


use HeimrichHannot\QuizBundle\Entity\QuizSession;

class InsertTags extends \Frontend
{

    const TOTAL_SCORE = 'huh_quiz_total_score';

    const CURRENT_SCORE = 'huh_quiz_current_score';

    /**
     * @param $strTag
     *
     * @return bool|int|mixed
     */
    public function quizInsertTags($strTag)
    {
        // Parameter abtrennen
        $arrSplit = explode('::', $strTag);

        if ($arrSplit[0] == static::TOTAL_SCORE && isset($arrSplit[1])) {
            return \System::getContainer()->get('huh.quiz.question.manager')->countPublishedByPid($arrSplit[1]);
        }

        if ($arrSplit[0] == static::CURRENT_SCORE) {

            return $this->getCurrentScore();
        }

        return false;
    }

    /**
     * returns the current quiz score from session
     *
     * @return int
     */
    public function getCurrentScore()
    {
        $session = New QuizSession();

        $score = $session->getData(QuizSession::SCORE_NAME);

        if (empty($score)) {
            return 0;
        }

        return $score;
    }
}