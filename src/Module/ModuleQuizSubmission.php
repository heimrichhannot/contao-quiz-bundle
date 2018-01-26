<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Module;


use HeimrichHannot\QuizBundle\Model\QuizModel;
use HeimrichHannot\Request\Request;
use HeimrichHannot\Submissions\Creator\ModuleSubmissionReader;
use HeimrichHannot\Submissions\SubmissionArchiveModel;
use Patchwork\Utf8;

class ModuleQuizSubmission extends ModuleSubmissionReader
{

    protected $strTemplate  = 'mod_quiz_submission';
    protected $strFormClass = 'HeimrichHannot\QuizBundle\Form\QuizSubmissionForm';

    /**
     * @var QuizModel
     */
    protected $quizModel;

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

        if (Request::hasGet('s')) {
            $this->quizModel                      = \System::getContainer()->get('huh.quiz.manager')->findByIdOrAlias(Request::getGet('items'));
            $this->formHybridDataContainer        = 'tl_submission';
            $submissionArchive                    = SubmissionArchiveModel::findBy('id', $this->quizModel->submissionArchive);
            $this->formHybridEditable             = $submissionArchive->submissionFields;
            $this->formHybridSingleSubmission     = $this->quizModel->formHybridSingleSubmission;
            $this->formHybridResetAfterSubmission = $this->quizModel->formHybridResetAfterSubmission;
        }

        return parent::generate();
    }

    protected function compile()
    {
        if ($this->quizModel->addSubmission) {
            return parent::compile();
        }
    }
}