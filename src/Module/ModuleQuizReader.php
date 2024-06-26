<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Module;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\Controller;
use Contao\Input;
use Contao\Module;
use Contao\System;
use Haste\Util\Url;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use HeimrichHannot\QuizBundle\Entity\QuizSession;
use HeimrichHannot\QuizBundle\Manager\TokenManager;
use HeimrichHannot\Request\Request;

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
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }
        $this->session = new QuizSession();

        // Set the item from the auto_item parameter
        if (!isset($_GET['items']) && isset($_GET['auto_item']) && Config::get('useAutoItem'))
        {
            Input::setGet('items', Input::get('auto_item'));
        }

        if ($this->singleQuiz) {
            $this->quiz = $this->quizArchive;
        } elseif (Request::hasGet('items')) {
            $this->quiz = Request::getGet('items');
        }

        if ($this->quiz <= 0 && $this->quizArchive) {
            $this->quiz = $this->quizArchive;
        }

        $tokenManager = System::getContainer()->get('huh.quiz.token.manager');
        if (Request::hasGet('token')) {
            $this->token = Request::getGet('token');
            $data = $tokenManager->getDataFromJwtToken($this->token);
            if (!$data->quizId) {
                $token = $tokenManager->addDataToJwtToken($this->token, $this->quiz, TokenManager::QUIZ_NAME);
                $url = System::getContainer()->get('contao.framework')->getAdapter(Url::class)->addQueryString('token='.$token, System::getContainer()->get('request_stack')->getCurrentRequest()->getUri());
                System::getContainer()->get('contao.framework')->getAdapter(Controller::class)->redirect($url);
            }
        } else {
            $tokenManager->addDataToJwtToken($this->token, $this->quiz, TokenManager::QUIZ_NAME);
        }

        if (System::getContainer()->has(HtmlHeadTagManager::class)) {
            $headTagManager = System::getContainer()->get(HtmlHeadTagManager::class);

        }

        if (System::getContainer()->get('huh.utils.container')->isBundleActive('HeimrichHannotContaoHeadBundle')) {
            System::getContainer()->get('huh.head.tag.link_canonical')->setContent(System::getContainer()->get('huh.utils.url')->getCurrentUrl(['skipParams' => 'token']));
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
            $reachablePoints = System::getContainer()->get('huh.quiz.question.manager')->getMaxReachablePointsPerQuiz($this->quiz);

            return $this->Template->quiz = System::getContainer()->get('huh.quiz.evaluation.manager')->parseQuizEvaluation($this->quiz, $reachablePoints, $this->token);
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
