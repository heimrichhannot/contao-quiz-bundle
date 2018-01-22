<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Module;


use Contao\Module;
use Contao\System;
use HeimrichHannot\QuizBundle\Entity\QuizSession;
use HeimrichHannot\QuizBundle\Form\QuizForm;
use HeimrichHannot\QuizBundle\Model\QuizAnswerModel;
use HeimrichHannot\QuizBundle\Model\QuizAnswerSolvingModel;
use HeimrichHannot\QuizBundle\Model\QuizModel;
use HeimrichHannot\QuizBundle\Model\QuizQuestionModel;
use Patchwork\Utf8;
use Symfony\Component\Form\Forms;

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

        return parent::generate();
    }

    protected function compile()
    {
        $quiz = $this->quiz;

        if ($quiz <= 0) {
            return '';
        }

        $quizModel = QuizModel::findOneBy('id', $quiz);

        if (null == $quizModel) {
            return '';
        }

        // apply module fields to template
        $this->Template->headline = $this->headline;
        $this->Template->hl       = $this->hl;
        $this->count              = $questionCollection = QuizQuestionModel::findBy('pid', $quizModel->id)->count();

        $sessionData = current($this->session->getData('questionId'));

        if (!empty($sessionData)) {

            $question = QuizQuestionModel::findBy('id', $sessionData);

            return $this->Template->form = $this->prepareQuestion($question);
        }

        $questionCollection = QuizQuestionModel::findBy('pid', $quizModel->id);

        if (null == $questionCollection) {
            return '';
        }
        $this->setQuestionQueue($questionCollection);
        $this->session->reset('questionId');
        $this->session->reset(QuizSession::SCORE_NAME);

        $this->session->setData('current', [1]);
        $this->Template->form = $this->prepareQuestion($questionCollection);
    }

    /**
     * @param $question
     *
     * @return string
     */
    protected function prepareQuestion($question)
    {
        /**
         * @var \Contao\PageModel $objPage
         */
        global $objPage;

        $answersCollection = QuizAnswerModel::findBy('pid', $question->id);

        if (null == $answersCollection) {
            return '';
        }

        /**
         * @var \Twig_Environment
         */
        $twig = System::getContainer()->get('twig');

        $formData[QuizForm::ANSWERS_NAME]          = $this->prepareAnswers($answersCollection);
        $formData[QuizForm::QUESTION_NAME]['text'] = $question->question;
        $formData[QuizForm::QUESTION_NAME]['id']   = $question->id;
        $factory                                   = Forms::createFormFactoryBuilder()->addExtensions([])->getFormFactory();
        $form                                      = $factory->create(QuizForm::class, $formData);

        $form->handleRequest();

        if ($form->isSubmitted() && $form->isValid()) {
            $data          = $form->getData();
            $answer        = QuizAnswerModel::findBy('id', $data[QuizForm::ANSWER_NAME]);
            $answerSolving = QuizAnswerSolvingModel::findBy('pid', $answer->id);
            $solving       = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.wrong');

            if ($answer->isSolution) {
                $this->increaseScore();
                $solving = System::getContainer()->get('translator')->trans('huh.quiz.answer.solving.correct');
            }

            if (null !== $answerSolving) {
                $solving = $answerSolving->solving;
            }


            $queue = $this->session->getData('queue');

            $questionId = $this->session->getData('questionId');

            if (empty($questionId)) {
                $this->session->setData('questionId', [1 => $queue[1]]);
            } else {
                $key = key($questionId);
                $key = $key + 1;
                if (isset($queue[$key]) && !empty($queue[$key])) {
                    $this->session->setData('questionId', [$key => $queue[$key]]);
                } else {
                    $this->session->reset('questionId');
                }
            }

            $current = current($this->session->getData('current'));
            $this->session->setData('current', [$current + 1]);

            return $twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_answer_solving.html.twig', [
                'answerSolving' => $solving,
                'nextUrl'       => $objPage->getFrontendUrl(),
            ]);
        }

        // item count text
        $itemsFoundText = System::getContainer()->get('translator')->transChoice('huh.quiz.count.text.default', $this->count, ['%current%' => current($this->session->getData('current')), '%count%' => $this->count]);

        return $twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_form_div_layout.html.twig', [
            'form'           => $form->createView(),
            'itemsFoundText' => $itemsFoundText,
        ]);
    }

    /**
     * @param $answersCollection
     *
     * @return array
     */
    protected function prepareAnswers($answersCollection)
    {
        $answers = [];

        foreach ($answersCollection as $answerModel) {
            $answers[$answerModel->id] = $answerModel->answer;
        }

        return $answers;
    }

    protected function increaseScore()
    {
        $score = $this->session->getData(QuizSession::SCORE_NAME);
        if (empty($score)) {
            $score = 1;
        } else {
            $score = $score[0] + 1;
        }
        $this->session->setData(QuizSession::SCORE_NAME, [$score]);
    }

    /**
     * @param $questions
     */
    protected function setQuestionQueue($questions)
    {
        $queue = [];
        foreach ($questions as $question) {
            $queue[] = $question->id;
        }
        $this->session->setData('queue', $queue);
    }
}