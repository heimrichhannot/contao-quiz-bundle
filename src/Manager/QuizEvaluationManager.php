<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\QuizBundle\Model\QuizEvaluationModel;

class QuizEvaluationManager extends Manager
{
    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        parent::__construct($framework);
        $this->class = QuizEvaluationModel::class;
    }

    /**
     * @param $quizId
     * @param $count
     *
     * @return mixed
     */
    public function parseQuizEvaluation($quizId, $count, $token)
    {
        /*
         * @var \Twig_Environment
         */
        $twig = System::getContainer()->get('twig');
        $score = System::getContainer()->get('huh.quiz.token.manager')->getCurrentScore($token);
        $templateData['score'] = System::getContainer()->get('translator')->transChoice('huh.quiz.answer.score', $score, ['%score%' => $score, '%possibleScore%' => $count]);
        $quizEvaluationModel = $this->findPublishedByPid($quizId);
        $quiz = System::getContainer()->get('huh.quiz.manager')->findOneBy('id', $quizId);
        $templateData['text'] = $quiz->text;
        $templateData['title'] = $quiz->title;
        if (null === $quizEvaluationModel) {
            return $twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_evaluation.html.twig', $templateData);
        }
        $templateData['evaluation'] = '';
        foreach ($quizEvaluationModel as $item) {
            $templateData['evaluation'] .= System::getContainer()->get('huh.quiz.model.manager')->parseModel($item, $item->evaluationText, QuizEvaluationModel::getTable(), $item->cssClass, $item->imgSize);
        }

        return $twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_evaluation.html.twig', $templateData);
    }
}
