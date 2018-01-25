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
use HeimrichHannot\QuizBundle\Model\QuizScoreModel;
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

    /**
     * @var string $question
     */
    protected $question;

    /**
     * @var string $answer
     */
    protected $answer;

    /**
     * @var string $score
     */
    protected $score;

    /**
     * @var string $quiz
     */
    protected $quiz;

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

        // Set the item from the auto_item parameter
        if (!isset($_GET['items']) && \Config::get('useAutoItem') && isset($_GET['auto_item'])) {
            Request::setGet('items', Request::getGet('auto_item'));
        }

        // Do not index or cache the page if no news item has been specified
        if (!Request::hasGet('items')) {

            /** @var \PageModel $objPage */
            global $objPage;

            $objPage->noSearch = 1;
            $objPage->cache    = 0;

            return '';
        }

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
        $this->quiz = Request::getGet('items');

        if ($this->quiz <= 0) {
            return $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.error');
        }

        $quizModel = \System::getContainer()->get('huh.quiz.manager')->findOneBy('id', $this->quiz);

        if (null == $quizModel) {
            return $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.error');
        }

        // apply module fields to template
        $this->Template->headline = $this->headline;
        $this->Template->hl       = $this->hl;
        $this->count              = \System::getContainer()->get('huh.quiz.question.manager')->countPublishedByPid($quizModel->id);

        if ($this->answer) {
            return $this->Template->quiz = $this->parseAnswerSolving($this->answer);
        }

        if ($this->score) {
            return $this->Template->quiz = $this->parseQuizScore();
        }

        if ($this->question) {
            $question = \System::getContainer()->get('huh.quiz.question.manager')->findOnePublishedByPid($this->question);

            if (null == $question) {
                return $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.error');
            }

            return $this->Template->quiz = $this->prepareQuestion($question);
        }

        $questionModel = \System::getContainer()->get('huh.quiz.question.manager')->findOnePublishedByPid($quizModel->id);

        if (null == $questionModel) {
            return $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.error');
        }
        $this->session->reset(QuizSession::USED_QUESTIONS_NAME);
        $this->session->reset(QuizSession::SCORE_NAME);

        $this->Template->quiz = $this->prepareQuestion($questionModel);
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
            $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.error');
        }

        $templateData['answers'] = $this->prepareAnswers($answersCollection);

        $this->addCurrentQuestionToSession($question->id);

        // item count text
        $templateData['itemsFoundText'] = System::getContainer()->get('translator')->transChoice('huh.quiz.count.text.default', $this->count, ['%current%' => count($this->session->getData(QuizSession::USED_QUESTIONS_NAME)), '%count%' => $this->count]);


        $templateData['question'] = $this->parseModel($question, $question->question);

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
        $templateData['answer'] = $this->parseModel($answerModel, $answerModel->answer);

        $templateData['href'] = $this->generateUrl('a', $answerModel->id);

        return $this->twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_answer_item.html.twig', $templateData);
    }

    /**
     * @param $pid
     *
     * @return string
     */
    protected function parseAnswerSolving($pid)
    {
        $answer = \System::getContainer()->get('huh.quiz.answer.manager')->findBy('id', $this->answer);
        if (null == $answer) {
            return System::getContainer()->get('translator')->trans('huh.quiz.error');
        }
        $solving = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.wrong');

        if ($answer->isSolution) {
            $this->session->increaseScore();
            $solving = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.correct');
        }

        $templateData  = $this->getNextQuestionUrl();
        $answerSolving = \System::getContainer()->get('huh.quiz.answer.solving.manager')->findPublishedByPid($pid);

        if (null !== $answerSolving) {
            $solving = '';
            foreach ($answerSolving as $item) {
                $solving .= $this->parseModel($item, $item->solving);
            }
        }
        $templateData['answerSolving'] = $solving;

        return $this->twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_answer_solving.html.twig', $templateData);
    }

    /**
     * @return array $templateData
     */
    protected function getNextQuestionUrl()
    {
        $usedQuestions            = $this->session->getData(QuizSession::USED_QUESTIONS_NAME);
        $questionModel            = \System::getContainer()->get('huh.quiz.question.manager')->findOnePublishedByPidNotInQuestions($this->quiz, $usedQuestions);
        $templateData['linkText'] = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.next');
        if (null == $questionModel) {
            $this->session->reset(QuizSession::USED_QUESTIONS_NAME);
            $score = $this->session->getData(QuizSession::SCORE_NAME);
            if (null == $score) {
                $score = 0;
            }
            $templateData['href']     = $this->generateUrl('s', $this->quiz);
            $templateData['linkText'] = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.score');
        } else {
            $templateData['href'] = $this->generateUrl('q', $questionModel->id);
        }

        return $templateData;
    }

    /**
     * returns the urls as string
     *
     * @param $param
     * @param $value
     *
     * @return mixed|string
     */
    protected function generateUrl($param, $value)
    {
        /**
         * @var \Contao\PageModel $objPage
         */
        global $objPage;

        return ampersand($objPage->getFrontendUrl() . '/' . $this->quiz . '?' . $param . '=' . $value);
    }

    /**
     * get the quiz score
     *
     * @return string
     */
    protected function parseQuizScore()
    {
        $score                 = $this->session->getData(QuizSession::SCORE_NAME);
        $templateData['score'] = System::getContainer()->get('translator')->transChoice('huh.quiz.answer.score', $score, ['%score%' => $score, '%possibleScore%' => $this->count]);
        $quizScoreModel        = \System::getContainer()->get('huh.quiz.score.manager')->findPublishedByPid($this->score);
        if (null == $quizScoreModel) {
            return $this->twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_score.html.twig', $templateData);
        }
        foreach ($quizScoreModel as $item) {
            $templateData['strTemplate'] .= $this->parseModel($item, $item->scoreText);
        }

        return $this->twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_score.html.twig', $templateData);
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function parseModel($item, $text)
    {
        $templateData['text']              = $text;
        $item                              = $this->getContentElementByModel($item, QuizScoreModel::getTable());
        $templateData['item']              = $item;
        $templateData['hasContentElement'] = $item->hasContentElement;
        $templateData['contentElement']    = $item->contentElement;
        $this->addImage($item, $templateData);

        return $this->twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_item.html.twig', $templateData);
    }
}