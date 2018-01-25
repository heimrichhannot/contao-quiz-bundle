<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

/**
 * Dynamically add the permission check and parent table
 */
if (Input::get('do') == 'quiz') {

    switch (\HeimrichHannot\Request\Request::getGet('ptable')) {
        case 'tl_quiz_question';
            $GLOBALS['TL_DCA']['tl_content']['config']['ptable']                                = 'tl_quiz_question';
            $GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][]                     = \System::getContainer()->get('huh.quiz.backend.content')->checkPermission('tl_quiz_question', 'tl_quiz');
            $GLOBALS['TL_DCA']['tl_content']['list']['operations']['toggle']['button_callback'] = ['tl_content_quiz', 'toggleIcon'];
            break;
        case 'tl_quiz_answer';
            $GLOBALS['TL_DCA']['tl_content']['config']['ptable']                                = 'tl_quiz_answer';
            $GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][]                     = \System::getContainer()->get('huh.quiz.backend.content')->checkPermission('tl_quiz_answer', 'tl_quiz_question');
            $GLOBALS['TL_DCA']['tl_content']['list']['operations']['toggle']['button_callback'] = ['tl_content_quiz', 'toggleIcon'];
            break;
        case 'tl_quiz_answer_solving';
            $GLOBALS['TL_DCA']['tl_content']['config']['ptable']                                = 'tl_quiz_answer';
            $GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][]                     = \System::getContainer()->get('huh.quiz.backend.content')->checkPermission('tl_quiz_answer_solving', 'tl_quiz_answer');
            $GLOBALS['TL_DCA']['tl_content']['list']['operations']['toggle']['button_callback'] = ['tl_content_quiz', 'toggleIcon'];
            break;
        case 'tl_quiz_evaluation';
            $GLOBALS['TL_DCA']['tl_content']['config']['ptable']                                = 'tl_quiz_evaluation';
            $GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][]                     = \System::getContainer()->get('huh.quiz.backend.content')->checkPermission('tl_quiz_evaluation', 'tl_quiz');
            $GLOBALS['TL_DCA']['tl_content']['list']['operations']['toggle']['button_callback'] = ['tl_content_quiz', 'toggleIcon'];
            break;
    }
}

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 */
class tl_content_quiz extends Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (\strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_content::invisible', 'alexf')) {
            return '';
        }

        $href .= '&amp;id=' . Input::get('id') . '&amp;tid=' . $row['id'] . '&amp;state=' . $row['invisible'];

        if ($row['invisible']) {
            $icon = 'invisible.svg';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['invisible'] ? 0 : 1) . '"') . '</a> ';
    }


    /**
     * Toggle the visibility of an element
     *
     * @param integer       $intId
     * @param boolean       $blnVisible
     * @param DataContainer $dc
     */
    public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$this->User->hasAccess('tl_content::invisible', 'alexf')) {
            throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish content element ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc) {
            $objRow = $this->Database->prepare("SELECT * FROM tl_content WHERE id=?")->limit(1)->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new Versions('tl_content', $intId);
        $objVersions->initialize();

        // Reverse the logic (elements have invisible=1)
        $blnVisible = !$blnVisible;

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_content']['fields']['invisible']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_content']['fields']['invisible']['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                } elseif (\is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $this->Database->prepare("UPDATE tl_content SET tstamp=$time, invisible='" . ($blnVisible ? '1' : '') . "' WHERE id=?")->execute($intId);

        if ($dc) {
            $dc->activeRecord->tstamp    = $time;
            $dc->activeRecord->invisible = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }
}
