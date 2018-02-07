<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Module;

use Contao\Module;
use Contao\System;
use HeimrichHannot\QuizBundle\Entity\QuizSession;
use HeimrichHannot\Request\Request;
use Patchwork\Utf8;

class ModuleQuizReader extends Module
{
    protected $strTemplate = 'mod_quiz';

    /**
     * @var int
     */
    protected $count;

    /**
     * @var QuizSession
     */
    protected $session;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $quiz;

    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }
        $this->session = new QuizSession();

        // Set the item from the auto_item parameter
        if (!isset($_GET['items']) && \Config::get('useAutoItem') && isset($_GET['auto_item'])) {
            Request::setGet('items', Request::getGet('auto_item'));
        }

        if (Request::hasGet('items')) {
            $this->quiz = Request::getGet('items');
        }

        if ($this->quiz <= 0 && $this->quizArchive) {
            $this->quiz = $this->quizArchive;
        }

        if (Request::hasGet('token')) {
            $this->token = Request::getGet('token');
        }

        return parent::generate();
    }

    protected function compile()
    {
        if ($this->quiz <= 0) {
            return $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.error');
        }

        $quizModel = \System::getContainer()->get('huh.quiz.manager')->findOneBy('id', $this->quiz);
        // apply module fields to template
        $this->Template->headline = $this->headline;
        $this->Template->hl = $this->hl;
        $this->Template->title = $quizModel->title;
        $this->Template->text = $quizModel->text;

        if (null === $quizModel) {
            return $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.error');
        }

        $this->count = System::getContainer()->get('huh.quiz.question.manager')->countByPid($quizModel->id);

        if (Request::hasGet('answer')) {
            return $this->Template->quiz = System::getContainer()->get('huh.quiz.answer.solving.manager')->parseAnswerSolving(Request::getGet('answer'), $this->quiz, $this->token);
        }

        if (Request::hasGet('finished')) {
            return $this->Template->quiz = System::getContainer()->get('huh.quiz.evaluation.manager')->parseQuizEvaluation($this->quiz, $this->count, $this->token);
        }

        if (Request::hasGet('question')) {
            $question = System::getContainer()->get('huh.quiz.question.manager')->findBy('id', Request::getGet('question'));

            if (null === $question) {
                return $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.question.error');
            }

            return $this->Template->quiz = System::getContainer()->get('huh.quiz.question.manager')->prepareQuestion($question, $quizModel, $this->count, $this->imgSize);
        }

        $questionModel = System::getContainer()->get('huh.quiz.question.manager')->findOnePublishedByPid($quizModel->id);

        if (null === $questionModel) {
            return $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.question.error');
        }
        $this->session->reset(QuizSession::USED_QUESTIONS_NAME);

        $this->Template->quiz = System::getContainer()->get('huh.quiz.question.manager')->prepareQuestion($questionModel, $quizModel, $this->count, $this->imgSize);
    }
}
