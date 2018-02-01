<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Frontend;

use Contao\Controller;
use Contao\Frontend;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\QuizBundle\Entity\QuizSession;

class InsertTags extends Frontend
{
    const TOTAL_SCORE = 'huh_quiz_total_score';

    const CURRENT_SCORE = 'huh_quiz_current_score';

    const QUIZ = 'huh_quiz';

    /**
     * @param $strTag
     *
     * @return bool|int|mixed
     */
    public function quizInsertTags($strTag)
    {
        // Parameter abtrennen
        $arrSplit = explode('::', $strTag);

        if ($arrSplit[0] === static::TOTAL_SCORE && isset($arrSplit[1])) {
            return System::getContainer()->get('huh.quiz.question.manager')->countByPid($arrSplit[1]);
        }

        if ($arrSplit[0] === static::CURRENT_SCORE) {
            $session = new QuizSession();

            return $session->getCurrentScore();
        }

        if ($arrSplit[0] === static::QUIZ && isset($arrSplit[1]) && isset($arrSplit[2])) {
            return $this->getQuiz($arrSplit[1], $arrSplit[2]);
        }

        return false;
    }

    /**
     * @param $moduleId
     * @param $quizId
     *
     * @return string
     */
    public function getQuiz($moduleId, $quizId)
    {
        $moduleModel = System::getContainer()->get('contao.framework')->getAdapter(ModuleModel::class)->findByIdOrAlias($moduleId);
        $moduleModel->quizArchive = $quizId;

        return System::getContainer()->get('contao.framework')->getAdapter(Controller::class)->getFrontendModule($moduleModel);
    }
}
