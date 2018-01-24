<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Module;


use Contao\Model;
use Contao\Module;
use Contao\System;
use HeimrichHannot\QuizBundle\Entity\QuizSession;
use HeimrichHannot\QuizBundle\Model\QuizAnswerModel;
use HeimrichHannot\QuizBundle\Model\QuizQuestionModel;
use Contao\Model\Collection;
use HeimrichHannot\Request\Request;
use Patchwork\Utf8;

class ModuleQuiz extends Module
{

    protected $strTemplate = 'mod_quiz';

    /**
     * @var integer
     */
    protected $count;

    /**
     * @var QuizSession $session
     */
    protected $session;

    protected $question;

    protected $answer;

    protected $score;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate           = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]) . ' ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }
        $this->session = new QuizSession();

        if (Request::hasGet('q')) {
            $this->question = Request::getGet('q');
        }

        if (Request::hasGet('a')) {
            $this->answer = Request::getGet('a');
        }

        if (Request::hasGet('s')) {
            $this->score = Request::getGet('s');
        }

        /**
         * @var \Twig_Environment
         */
        $this->twig = System::getContainer()->get('twig');

        return parent::generate();
    }

    protected function compile()
    {
        $quiz = $this->quiz;

        if ($quiz <= 0) {
            return '';
        }

        $quizModel = \System::getContainer()->get('huh.quiz.manager')->findOneBy('id', $quiz);

        if (null == $quizModel) {
            return '';
        }

        // apply module fields to template
        $this->Template->headline = $this->headline;
        $this->Template->hl       = $this->hl;
        $this->count              = \System::getContainer()->get('huh.quiz.question.manager')->findPublishedByPid($quizModel->id)->count();

        if ($this->answer) {
            $answer = \System::getContainer()->get('huh.quiz.answer.manager')->findBy('id', $this->answer);
            if (null == $answer) {
                return '';
            }
            $answerSolving = \System::getContainer()->get('huh.quiz.answer.solving.manager')->findOneBy('pid', $answer->id);

            return $this->Template->form = $this->parseAnswerSolving($answerSolving, $answer->isSolution);
        }

        if ($this->score) {
            return $this->Template->form = current($this->session->getData(QuizSession::SCORE_NAME));
        }

        if ($this->question) {
            $question = \System::getContainer()->get('huh.quiz.question.manager')->findOneBy('id', $this->question);

            return $this->Template->form = $this->prepareQuestion($question);
        }

        $questionModel = \System::getContainer()->get('huh.quiz.question.manager')->findOneBy('pid', $quizModel->id);

        if (null == $questionModel) {
            return '';
        }
        $this->session->reset(QuizSession::USED_QUESTIONS_NAME);
        $this->session->reset(QuizSession::SCORE_NAME);

        $this->Template->form = $this->prepareQuestion($questionModel);
    }

    /**
     * @param $question
     *
     * @return string
     */
    protected function prepareQuestion($question)
    {
        $answersCollection = \System::getContainer()->get('huh.quiz.answer.manager')->findPublishedByPid($question->id);

        if (null == $answersCollection) {
            return '';
        }

        $question                      = $this->getContentElementByModel($question, QuizQuestionModel::getTable());
        $templateData['questionModel'] = $question;
        $templateData['answers']       = $this->prepareAnswers($answersCollection);

        $this->addCurrentQuestionToSession($question->id);
        $this->addImage($question, $templateData);

        // item count text
        $templateData['itemsFoundText']    = System::getContainer()->get('translator')->transChoice('huh.quiz.count.text.default', $this->count, ['%current%' => count($this->session->getData(QuizSession::USED_QUESTIONS_NAME)), '%count%' => $this->count]);
        $templateData['hasContentElement'] = $question->hasContentElement;
        $templateData['contentElement']    = $question->contentElement;
        $templateData['question']          = $question->question;

        return $this->twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_question.html.twig', $templateData);
    }

    /**
     * @param $answersCollection
     *
     * @return array
     */
    protected function prepareAnswers(Collection $answersCollection)
    {
        $answers = [];

        foreach ($answersCollection as $answer) {
            $answers[$answer->id] = $this->parseAnswer($answer);;
        }

        return $answers;
    }

    /**
     * @param $questionId
     */
    protected function addCurrentQuestionToSession($questionId)
    {
        $usedQuestions              = $this->session->getData(QuizSession::USED_QUESTIONS_NAME);
        $usedQuestions[$questionId] = $questionId;
        $this->session->setData(QuizSession::USED_QUESTIONS_NAME, $usedQuestions);
    }

    /**
     * gets the content element by the given model and table
     *
     * @param Model  $objModel
     * @param string $table
     *
     * @return Model $objModel
     */
    protected function getContentElementByModel(Model $objModel, string $table)
    {
        $id = $objModel->id;

        $strText    = '';
        $objElement = \ContentModel::findPublishedByPidAndTable($id, $table);
        if ($objElement !== null) {
            while ($objElement->next()) {
                $strText .= $this->getContentElement($objElement->current());
            }
        }

        $objModel->contentElement    = $strText;
        $objModel->hasContentElement = \ContentModel::countPublishedByPidAndTable($objModel->id, $table) > 0;

        return $objModel;
    }

    /**
     * add image to given model
     *
     * @param       $objArticle
     * @param array $templateData
     */
    protected function addImage($objArticle, array &$templateData)
    {
        // Add an image
        if ($objArticle->addImage && $objArticle->singleSRC != '') {
            $imageModel = \FilesModel::findByUuid($objArticle->singleSRC);

            if ($imageModel !== null && is_file(TL_ROOT . '/' . $imageModel->path)) {
                // Do not override the field now that we have a model registry (see #6303)
                $imageArray = $objArticle->row();

                // Override the default image size
                if ($this->imgSize != '') {
                    $size = \StringUtil::deserialize($this->imgSize);

                    if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                        $imageArray['size'] = $this->imgSize;
                    }
                }
                $imageArray['singleSRC']             = $imageModel->path;
                $templateData['images']['singleSRC'] = [];
                System::getContainer()->get('huh.utils.image')->addToTemplateData('singleSRC', 'addImage', $templateData['images']['singleSRC'], $imageArray, null, null, null, $imageModel);
            }
        }
    }

    /**
     * parse the answer and return twig template as string
     *
     * @param QuizAnswerModel $answerModel
     *
     * @return string
     */
    protected function parseAnswer(QuizAnswerModel $answerModel)
    {
        /**
         * @var \Contao\PageModel $objPage
         */
        global $objPage;

        $answerModel                       = $this->getContentElementByModel($answerModel, QuizAnswerModel::getTable());
        $templateData['answer']            = $answerModel;
        $templateData['answerText']        = $answerModel->answer;
        $templateData['hasContentElement'] = $answerModel->hasContentElement;
        $templateData['contentElement']    = $answerModel->contentElement;
        $templateData['href']              = $objPage->getFrontendUrl() . '?a=' . $answerModel->id;
        $this->addImage($answerModel, $templateData);

        return $this->twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_answer_item.html.twig', $templateData);
    }

    /**
     * @param $answerSolving
     *
     * @return string
     */
    protected function parseAnswerSolving($answerSolving, $isSolution)
    {
        /**
         * @var \Contao\PageModel $objPage
         */
        global $objPage;

        $solving = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.wrong');

        if ($isSolution) {
            $this->session->increaseScore();
            $solving = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.correct');
        }

        $usedQuestions = $this->session->getData(QuizSession::USED_QUESTIONS_NAME);

        $questionModel = \System::getContainer()->get('huh.quiz.question.manager')->findOnePublishedByPidNotInQuestions($this->quiz, $usedQuestions);

        if (null == $questionModel) {
            $this->session->reset(QuizSession::USED_QUESTIONS_NAME);
            $templateData['href'] = $objPage->getFrontendUrl() . '?s=' . current($this->session->getData(QuizSession::SCORE_NAME));
        } else {
            $templateData['href'] = $objPage->getFrontendUrl() . '?q=' . $questionModel->id;
        }

        if (null !== $answerSolving) {
            $solving = $answerSolving->solving;
            $this->addImage($answerSolving, $templateData);
        }
        $templateData['answerSolving'] = $solving;

        return $this->twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_answer_solving.html.twig', $templateData);
    }
}