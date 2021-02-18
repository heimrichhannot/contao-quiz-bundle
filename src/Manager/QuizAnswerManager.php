<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model\Collection;
use Contao\System;
use Haste\Util\Url;
use HeimrichHannot\QuizBundle\Model\QuizAnswerModel;

class QuizAnswerManager extends Manager
{
    /**
     * Constructor.
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        parent::__construct($framework);
        $this->class = QuizAnswerModel::class;
    }

    /**
     * @param $answersCollection
     *
     * @return array
     */
    public function prepareAnswers(Collection $answersCollection)
    {
        $answers = [];

        foreach ($answersCollection as $answer) {
            $answers[] = $this->parseAnswer($answer);
        }

        return $answers;
    }

    /**
     * parse the answer and return twig template as string.
     *
     * @return string
     */
    public function parseAnswer(QuizAnswerModel $answerModel)
    {
        /*
         * @var \Twig_Environment
         */
        $twig = System::getContainer()->get('twig');
        $answer = $answerModel->answer ?: $answerModel->title;
        $templateData['answer'] = System::getContainer()->get('huh.quiz.model.manager')
            ->parseModel($answerModel, $answer, QuizAnswerModel::getTable(), $answerModel->cssClass, $answerModel->imgSize);
        $templateData['href'] = $this->framework->getAdapter(Url::class)->addQueryString('answer='.$answerModel->id, $this->getUri());

        return $twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_answer_item.html.twig', $templateData);
    }
}
