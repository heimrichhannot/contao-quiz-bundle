<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Module;


use Contao\Module;
use Contao\System;
use Haste\Util\Url;
use HeimrichHannot\QuizBundle\Entity\QuizSession;
use HeimrichHannot\QuizBundle\Model\QuizAnswerModel;
use Contao\Model\Collection;
use HeimrichHannot\QuizBundle\Model\QuizAnswerSolvingModel;
use HeimrichHannot\QuizBundle\Model\QuizEvaluationModel;
use HeimrichHannot\QuizBundle\Model\QuizQuestionModel;
use HeimrichHannot\Request\Request;
use Patchwork\Utf8;

class ModuleQuizReader extends Module
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
     * @var string $token
     */
    protected $token;

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

        if (Request::hasGet('items')) {
            $this->quiz = Request::getGet('items');
        }

        if ($this->quizArchive) {
            $this->quiz = $this->quizArchive;
        }

        if (Request::hasGet('token')) {
            $this->token = Request::getGet('token');
        }

        /**
         * @var \Twig_Environment
         */
        $this->twig = System::getContainer()->get('twig');

        return parent::generate();
    }

    protected function compile()
    {
        if ($this->quiz <= 0) {
            return $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.error');
        }

        $quizModel = \System::getContainer()->get('huh.quiz.manager')->findByIdOrAlias($this->quiz);
        // apply module fields to template
        $this->Template->headline = $this->headline;
        $this->Template->hl       = $this->hl;

        if (null == $quizModel) {
            return $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.error');
        }

        $this->count = System::getContainer()->get('huh.quiz.question.manager')->countPublishedByPid($quizModel->id);

        if (Request::hasGet('answer')) {
            return $this->Template->quiz = $this->parseAnswerSolving(Request::getGet('answer'));
        }

        if (Request::hasGet('finished')) {
            return $this->Template->quiz = $this->parseQuizEvaluation();
        }

        if (Request::hasGet('question')) {
            $question = \System::getContainer()->get('huh.quiz.question.manager')->findByIdOrAlias(Request::getGet('question'));

            if (null == $question) {
                return $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.question.error');
            }

            return $this->Template->quiz = $this->prepareQuestion($question, $quizModel);
        }

        $questionModel = System::getContainer()->get('huh.quiz.question.manager')->findOnePublishedByPid($quizModel->id);

        if (null == $questionModel) {
            return $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.question.error');
        }
        $this->session->reset(QuizSession::USED_QUESTIONS_NAME);
        $this->session->reset(QuizSession::SCORE_NAME);

        $this->Template->quiz = $this->prepareQuestion($questionModel, $quizModel);
    }

    /**
     * @param $question
     * @param $quiz
     *
     * @return string
     */
    protected function prepareQuestion($question, $quiz)
    {
        $answersCollection = \System::getContainer()->get('huh.quiz.answer.manager')->findPublishedByPid($question->id);

        if (null == $answersCollection) {
            $this->Template->quiz = System::getContainer()->get('translator')->trans('huh.quiz.answer.error');
        }

        $templateData['answers'] = $this->prepareAnswers($answersCollection);

        $this->addCurrentQuestionToSession($question->id);

        // item count text
        $templateData['itemsFoundText'] = System::getContainer()->get('translator')->transChoice('huh.quiz.count.text.default', $this->count, ['%current%' => count($this->session->getData(QuizSession::USED_QUESTIONS_NAME)), '%count%' => $this->count]);
        $templateData['text']           = $quiz->text;
        $templateData['title']          = $quiz->title;
        $templateData['question']       = System::getContainer()->get('huh.quiz.model.manager')->parseModel($question, $question->question, QuizQuestionModel::getTable(), $question->cssClass, $this->imgSize);

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
            $answers[] = $this->parseAnswer($answer);
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
     * parse the answer and return twig template as string
     *
     * @param QuizAnswerModel $answerModel
     *
     * @return string
     */
    protected function parseAnswer(QuizAnswerModel $answerModel)
    {
        $templateData['answer'] = System::getContainer()->get('huh.quiz.model.manager')->parseModel($answerModel, $answerModel->answer, QuizAnswerModel::getTable(), $answerModel->cssClass, $this->imgSize);
        $templateData['href']   = Url::addQueryString('answer=' . $answerModel->id, $this->getUri());

        return $this->twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_answer_item.html.twig', $templateData);
    }

    /**
     * @param $pid
     *
     * @return string
     */
    protected function parseAnswerSolving($pid)
    {
        $answer = System::getContainer()->get('huh.quiz.answer.manager')->findBy('id', $pid);
        if (null == $answer) {
            return System::getContainer()->get('translator')->trans('huh.quiz.answer.error');
        }
        $solving = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.wrong');
        $token   = System::getContainer()->get('huh.quiz.token.manager')->addDataToJwtToken($this->token, $answer->id, $answer->pid);

        if ($answer->isSolution) {
            $this->session->increaseScore();
            $solving = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.correct');
        }

        $templateData  = $this->getNextQuestionUrl($token);
        $answerSolving = System::getContainer()->get('huh.quiz.answer.solving.manager')->findPublishedByPid($pid);

        if (null !== $answerSolving) {
            $solving = '';
            foreach ($answerSolving as $item) {
                $solving .= System::getContainer()->get('huh.quiz.model.manager')->parseModel($item, $item->solving, QuizAnswerSolvingModel::getTable(), $item->cssClass, $this->imgSize);
            }
        }
        $templateData['answerSolving'] = $solving;

        return $this->twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_answer_solving.html.twig', $templateData);
    }

    /**
     * @return array $templateData
     */
    protected function getNextQuestionUrl($token)
    {
        $usedQuestions            = $this->session->getData(QuizSession::USED_QUESTIONS_NAME);
        $questionModel            = System::getContainer()->get('huh.quiz.question.manager')->findOnePublishedByPidNotInQuestions($this->quiz, $usedQuestions);
        $templateData['linkText'] = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.next');
        if (null == $questionModel) {
            $this->session->reset(QuizSession::USED_QUESTIONS_NAME);
            $templateData['href']     = Url::addQueryString('finished=1' . '&token=' . $token, $this->getUri());
            $templateData['linkText'] = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.score');
        } else {
            $templateData['href'] = Url::addQueryString('question=' . $questionModel->id . '&token=' . $token, $this->getUri());
        }

        return $templateData;
    }

    /**
     * @return string
     */
    protected function getUri()
    {
        return Url::removeQueryString(['question', 'answer'], System::getContainer()->get('request_stack')->getCurrentRequest()->getUri());
    }

    /**
     * get the quiz evaluation
     *
     * @return string
     */
    protected function parseQuizEvaluation()
    {
        $score                 = $this->session->getData(QuizSession::SCORE_NAME);
        $templateData['score'] = System::getContainer()->get('translator')->transChoice('huh.quiz.answer.score', $score, ['%score%' => $score, '%possibleScore%' => $this->count]);
        $quizEvaluationModel   = System::getContainer()->get('huh.quiz.evaluation.manager')->findPublishedByPid($this->quiz);
        if (null == $quizEvaluationModel) {
            return $this->twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_evaluation.html.twig', $templateData);
        }
        foreach ($quizEvaluationModel as $item) {
            $templateData['evaluation'] .= System::getContainer()->get('huh.quiz.model.manager')->parseModel($item, $item->evaluationText, QuizEvaluationModel::getTable(), $item->cssClass, $this->imgSize);
        }

        return $this->twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_evaluation.html.twig', $templateData);
    }
}