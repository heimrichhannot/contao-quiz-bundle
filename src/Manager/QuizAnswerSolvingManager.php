<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Haste\Util\Url;
use HeimrichHannot\QuizBundle\Entity\QuizSession;
use HeimrichHannot\QuizBundle\Model\QuizAnswerSolvingModel;

class QuizAnswerSolvingManager extends Manager
{
    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        parent::__construct($framework);
        $this->class = QuizAnswerSolvingModel::class;
    }

    /**
     * @param $pid
     * @param $quizId
     * @param $token
     *
     * @return string
     */
    public function parseAnswerSolving($pid, $quizId, $token)
    {
        $answer = System::getContainer()->get('huh.quiz.answer.manager')->findBy('id', $pid);
        if (null === $answer) {
            return System::getContainer()->get('translator')->trans('huh.quiz.answer.error');
        }
        $solving = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.wrong');
        $token = System::getContainer()->get('huh.quiz.token.manager')->addDataToJwtToken($token, $answer->id, $answer->pid);

        if ($answer->isSolution) {
            $pointsPerQuestion = System::getContainer()->get('huh.quiz.question.manager')->getPointsPerQuestion($answer->pid);
            $token = System::getContainer()->get('huh.quiz.token.manager')->increaseScore($token, $pointsPerQuestion);
            $solving = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.correct');
        }

        $templateData = $this->getNextQuestionUrl($token, $quizId);
        $answerSolving = $this->findPublishedByPid($pid);

        if (null !== $answerSolving) {
            foreach ($answerSolving as $item) {
                $solving .= System::getContainer()->get('huh.quiz.model.manager')->parseModel($item, $item->solving, QuizAnswerSolvingModel::getTable(), $item->cssClass, $item->imgSize);
            }
        }
        $templateData['answerSolving'] = $solving;
        /*
         * @var \Twig_Environment
         */
        $twig = System::getContainer()->get('twig');

        return $twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_answer_solving.html.twig', $templateData);
    }

    /**
     * @param $token
     * @param $quizId
     *
     * @return mixed
     */
    public function getNextQuestionUrl($token, $quizId)
    {
        $usedQuestions = $this->session->getData(QuizSession::USED_QUESTIONS_NAME);
        $questionModel = System::getContainer()->get('huh.quiz.question.manager')->findOnePublishedByPidNotInQuestions($quizId, $usedQuestions);
        $templateData['linkText'] = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.next');
        if (null === $questionModel) {
            $token = System::getContainer()->get('huh.quiz.token.manager')->addDataToJwtToken($token, $quizId, 'quizId');
            $templateData['href'] = $this->framework->getAdapter(Url::class)->addQueryString('finished=1'.'&token='.$token, $this->getUri());
            $templateData['linkText'] = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.score');
        } else {
            $templateData['href'] = $this->framework->getAdapter(Url::class)->addQueryString('question='.$questionModel->id.'&token='.$token, $this->getUri());
        }

        return $templateData;
    }
}
