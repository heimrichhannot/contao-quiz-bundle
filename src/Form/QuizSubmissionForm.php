<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Form;

use HeimrichHannot\FrontendEdit\ReaderForm;
use HeimrichHannot\QuizBundle\Entity\QuizSession;
use HeimrichHannot\Request\Request;

class QuizSubmissionForm extends ReaderForm
{
    protected $objReaderModule;

    public function __construct($objConfig, array $submitCallbacks, $intId, $objReaderForm)
    {
        parent::__construct($objConfig, $submitCallbacks, $intId, $objReaderForm);
    }

    protected function onSubmitCallback(\DataContainer $dc)
    {
        $submission = $dc->getSubmission();

        if (Request::hasGet('token')) {
            $submission->quizToken = Request::getGet('token');
        }

        $quizSession = new QuizSession();
        $submission->quizScore = $quizSession->getData(QuizSession::SCORE_NAME);
        $submission->save();
    }
}
