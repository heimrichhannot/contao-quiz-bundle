<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Module;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\System;
use HeimrichHannot\QuizBundle\Model\QuizModel;
use HeimrichHannot\Request\Request;
use HeimrichHannot\Submissions\Creator\ModuleSubmissionReader;
use HeimrichHannot\Submissions\SubmissionArchiveModel;

class ModuleQuizSubmission extends ModuleSubmissionReader
{
    protected $strTemplate = 'mod_quiz_submission';
    protected $strFormClass = 'HeimrichHannot\QuizBundle\Form\QuizSubmissionForm';

    /**
     * @var QuizModel
     */
    protected $quizModel;

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

        // Set the item from the auto_item parameter
        if (!isset($_GET['items']) && Config::get('useAutoItem') && isset($_GET['auto_item'])) {
            Request::setGet('items', Request::getGet('auto_item'));
        }

        if (Request::hasGet('items')) {
            $this->quiz = Request::getGet('items');
        }

        if ($this->quiz <= 0 && Request::hasGet('token')) {
            $token = Request::getGet('token');
            $tokenData = System::getContainer()->get('huh.quiz.token.manager')->getDataFromJwtToken($token);
            if ($tokenData->quizId) {
                $this->quiz = $tokenData->quizId;
            }
        }

        if (!$this->quiz) {
            return '';
        }

        $this->quizModel = System::getContainer()->get('huh.quiz.manager')->findOneBy('id', $this->quiz);
        if (null === $this->quizModel) {
            return '';
        }
        $this->formHybridDataContainer = 'tl_submission';
        $submissionArchive = SubmissionArchiveModel::findBy('id', $this->quizModel->submissionArchive);
        if (null === $submissionArchive) {
            return '';
        }
        $this->formHybridEditable = $submissionArchive->submissionFields;
        $this->formHybridSingleSubmission = $this->quizModel->formHybridSingleSubmission;
        $this->formHybridResetAfterSubmission = $this->quizModel->formHybridResetAfterSubmission;
        $this->defaultArchive = $submissionArchive->id;

        return parent::generate();
    }

    protected function compile()
    {
        if ($this->quizModel->addSubmission) {
            return parent::compile();
        }
    }
}
