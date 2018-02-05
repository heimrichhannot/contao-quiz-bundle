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
use HeimrichHannot\QuizBundle\Model\QuizAnswerModel;
use Model\Collection;

class QuizAnswerManager extends Manager
{
    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
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
            $answers[] = $this->parseAnswer($answer, $answer->imgSize);
        }

        return $answers;
    }

    /**
     * parse the answer and return twig template as string.
     *
     * @param QuizAnswerModel $answerModel
     *
     * @return string
     */
    public function parseAnswer(QuizAnswerModel $answerModel, $imgSize)
    {
        /*
         * @var \Twig_Environment
         */
        $twig = System::getContainer()->get('twig');
        $templateData['answer'] = System::getContainer()->get('huh.quiz.model.manager')->parseModel($answerModel, $answerModel->answer, QuizAnswerModel::getTable(), $answerModel->cssClass, $imgSize);
        $templateData['href'] = $this->framework->getAdapter(Url::class)->addQueryString('answer='.$answerModel->id, $this->getUri());

        return $twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_answer_item.html.twig', $templateData);
    }
}
