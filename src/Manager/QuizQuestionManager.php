<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\QuizBundle\Entity\QuizSession;
use HeimrichHannot\QuizBundle\Model\QuizModel;
use HeimrichHannot\QuizBundle\Model\QuizQuestionModel;

class QuizQuestionManager extends Manager
{
    /**
     * Constructor.
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        parent::__construct($framework);
        $this->class = QuizQuestionModel::class;
    }

    /**
     * Find published questions items by their parent ID which are not in array.
     *
     * @param int   $intId      The quiz ID
     * @param int   $intLimit   An optional limit
     * @param array $arrOptions An optional options array
     * @param array $notIn
     *
     * @return \Model\Collection|QuizQuestionModel[]|QuizQuestionModel|null A collection of models or null if there are no news
     */
    public function findOnePublishedByPidNotInQuestions($intId, $notIn, $intLimit = 0, array $arrOptions = [])
    {
        /** @var QuizQuestionModel $adapter */
        $adapter = $this->framework->getAdapter(QuizQuestionModel::class);
        $t = $adapter->getTable();
        $arrColumns = ["$t.pid=?"];
        if (!$this->isPreviewMode($arrOptions)) {
            $time = \Date::floorToMinute();
            $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
        }
        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$t.dateAdded DESC";
        }
        if (!empty($notIn)) {
            $ids = implode(', ', $notIn);
            $arrColumns[] = "$t.id NOT IN ($ids)";
        }
        if ($intLimit > 0) {
            $arrOptions['limit'] = $intLimit;
        }

        return $adapter->findOneBy($arrColumns, $intId, $arrOptions);
    }

    /**
     * @param QuizQuestionModel $question
     * @param QuizModel         $quiz
     * @param int               $count
     * @param                   $imgSize
     *
     * @return mixed
     */
    public function prepareQuestion($question, $quiz, $count, $imgSize)
    {
        $answersCollection = System::getContainer()->get('huh.quiz.answer.manager')->findPublishedByPid($question->id);

        if (null === $answersCollection) {
            return System::getContainer()->get('translator')->trans('huh.quiz.answer.error');
        }

        $templateData['answers'] = System::getContainer()->get('huh.quiz.answer.manager')->prepareAnswers($answersCollection);

        $this->session->addCurrentQuestionToSession($question->id);

        // item count text
        $templateData['itemsFoundText'] = System::getContainer()->get('translator')
            ->transChoice('huh.quiz.count.text.default', $count, [
                '%current%' => \count($this->session->getData(QuizSession::USED_QUESTIONS_NAME)),
                '%count%' => $count, ]
            );
        $templateData['text'] = $quiz->text;
        $templateData['title'] = $quiz->title;
        $templateData['question'] = System::getContainer()->get('huh.quiz.model.manager')->parseModel($question, $question->question, QuizQuestionModel::getTable(), $question->cssClass, $imgSize);

        /*
         * @var \Twig_Environment
         */
        $twig = System::getContainer()->get('twig');

        return $twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_question.html.twig', $templateData);
    }

    /**
     * @param $id
     *
     * @return int
     */
    public function getPointsPerQuestion($id)
    {
        $question = $this->findBy('id', $id);

        if (null === $question) {
            return 0;
        }

        return $question->pointsPerQuestion;
    }

    /**
     * @param $quizId
     *
     * @return int
     */
    public function getMaxReachablePointsPerQuiz($quizId)
    {
        $questions = $this->findByPid($quizId);

        if (null === $questions) {
            return 0;
        }

        $points = 0;

        foreach ($questions as $question) {
            $points = $points + $question->pointsPerQuestion;
        }

        return $points;
    }
}
