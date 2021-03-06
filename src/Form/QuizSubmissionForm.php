<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Form;

use Contao\System;
use HeimrichHannot\FrontendEdit\ReaderForm;
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
            $submission->quizScore = System::getContainer()->get('huh.quiz.token.manager')->getCurrentScore(Request::getGet('token'));
        }
        $submission->save();
    }
}
