<?php

\Contao\Controller::loadDataContainer('tl_module');
\Contao\System::loadLanguageFile('tl_module');

$GLOBALS['TL_DCA']['tl_quiz_evaluation'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_quiz',
        'ctable'            => 'tl_content',
        'enableVersioning'  => true,
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting'           => [
            'mode'         => 1,
            'fields'       => ['title'],
            'headerFields' => ['title'],
            'panelLayout'  => 'filter;sort,search,limit',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['edit'],
                'href'  => 'table=tl_content&ptable=tl_quiz_evaluation',
                'icon'  => 'edit.gif',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.svg',
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['copy'],
                'href'  => 'act=paste&amp;mode=copy',
                'icon'  => 'copy.svg',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '))return false;Backend.getScrollOffset()"',
            ],
            'toggle'     => [
                'label'           => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_quiz_evaluation', 'toggleIcon'],
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => ['addImage', 'overwriteMeta', 'published'],
        'default'      => '{general_legend},title,author;{evaluation_legend},evaluationPerPointsMin,evaluationPerPointsMax,evaluationText;{image_legend},addImage;{expert_legend:hide},cssClass;{publish_legend},published',
    ],
    'subpalettes' => [
        'addImage'      => 'singleSRC,size,floating,imagemargin,fullsize,overwriteMeta',
        'overwriteMeta' => 'alt,imageTitle,imageUrl,caption',
        'published'     => 'start,stop',
    ],
    'fields'      => [
        'id'                     => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'                    => [
            'foreignKey' => 'tl_quiz_question.id',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp'                 => [
            'label' => &$GLOBALS['TL_LANG']['tl_quiz_answer']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded'              => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'published'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'start'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'stop'                   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'author'                 => [
            'label'      => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['author'],
            'default'    => BackendUser::getInstance()->id,
            'exclude'    => true,
            'search'     => true,
            'filter'     => true,
            'sorting'    => true,
            'flag'       => 11,
            'inputType'  => 'select',
            'foreignKey' => 'tl_user.name',
            'eval'       => ['doNotCopy' => true, 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'hasOne', 'load' => 'eager'],
        ],
        'cssClass'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['cssClass'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'addImage'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['addImage'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'overwriteMeta'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['overwriteMeta'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'singleSRC'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'mandatory' => true],
            'sql'       => "binary(16) NULL",
        ],
        'alt'                    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['alt'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'imageTitle'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['imageTitle'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'size'                   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_content']['size'],
            'exclude'          => true,
            'inputType'        => 'imageSize',
            'reference'        => &$GLOBALS['TL_LANG']['MSC'],
            'eval'             => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'options_callback' => function () {
                return System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser(BackendUser::getInstance());
            },
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'imagemargin'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['imagemargin'],
            'exclude'   => true,
            'inputType' => 'trbl',
            'options'   => $GLOBALS['TL_CSS_UNITS'],
            'eval'      => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'imageUrl'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['imageUrl'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'dcaPicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'caption'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['caption'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'allowHtml' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'floating'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['floating'],
            'default'   => 'above',
            'exclude'   => true,
            'inputType' => 'radioTable',
            'options'   => ['above', 'left', 'right', 'below'],
            'eval'      => ['cols' => 4, 'tl_class' => 'w50'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
        'fullsize'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['fullsize'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'evaluationText'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['evaluationText'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql'       => "text NULL",
        ],
        'evaluationPerPointsMin' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['evaluationPerPointsMin'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 4, 'tl_class' => 'w50'],
            'sql'       => "varchar(4) NOT NULL default ''",
        ],
        'evaluationPerPointsMax' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_evaluation']['evaluationPerPointsMax'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 4, 'tl_class' => 'w50'],
            'sql'       => "varchar(4) NOT NULL default ''",
        ],
    ],
];

class tl_quiz_evaluation extends \Contao\Backend
{

    public function listChildren($arrRow)
    {
        return '<div class="tl_content_left">' . ($arrRow['title'] ?: $arrRow['id']) . ' <span style="color:#b3b3b3; padding-left:3px">[' . \Date::parse(\Contao\Config::get('datimFormat'), trim($arrRow['dateAdded'])) . ']</span></div>';
    }

    public function checkPermission()
    {
        $user     = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!is_array($user->quizs) || empty($user->quizs)) {
            $root = [0];
        } else {
            $root = $user->quizs;
        }

        $id = strlen(\Contao\Input::get('id')) ? \Contao\Input::get('id') : CURRENT_ID;

        // Check current action
        switch (\Contao\Input::get('act')) {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!strlen(\Contao\Input::get('pid')) || !in_array(\Contao\Input::get('pid'), $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to create quiz_answer items in quiz_answer archive ID ' . \Contao\Input::get('pid') . '.');
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(\Contao\Input::get('pid'), $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Contao\Input::get('act') . ' quiz_answer item ID ' . $id . ' to quiz_answer archive ID ' . \Contao\Input::get('pid') . '.');
                }
            // NO BREAK STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $database->prepare("SELECT pid FROM tl_quiz_evaluation WHERE id=?")->limit(1)->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid quiz_answer item ID ' . $id . '.');
                }

                if (!in_array($objArchive->pid, $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Contao\Input::get('act') . ' quiz_answer item ID ' . $id . ' of quiz_answer archive ID ' . $objArchive->pid . '.');
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access quiz_answer archive ID ' . $id . '.');
                }

                $objArchive = $database->prepare("SELECT id FROM tl_quiz_evaluation WHERE pid=?")->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid quiz_answer archive ID ' . $id . '.');
                }

                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
                $session = \System::getContainer()->get('session');

                $session                   = $session->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $session->replace($session);
                break;

            default:
                if (strlen(\Contao\Input::get('act'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "' . \Contao\Input::get('act') . '".');
                } elseif (!in_array($id, $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access quiz_answer archive ID ' . $id . '.');
                }
                break;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \Contao\BackendUser::getInstance();

        if (strlen(\Contao\Input::get('tid'))) {
            $this->toggleVisibility(\Contao\Input::get('tid'), (\Contao\Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_quiz_evaluation::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.svg';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . \StringUtil::specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }

    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc = null)
    {
        $user     = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        // Set the ID and action
        \Contao\Input::setGet('id', $intId);
        \Contao\Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA']['tl_quiz_evaluation']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_quiz_evaluation']['config']['onload_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$user->hasAccess('tl_quiz_evaluation::published', 'alexf')) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish quiz_answer item ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc) {
            $objRow = $database->prepare("SELECT * FROM tl_quiz_evaluation WHERE id=?")->limit(1)->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new \Versions('tl_quiz_evaluation', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_quiz_evaluation']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_quiz_evaluation']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $database->prepare("UPDATE tl_quiz_evaluation SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")->execute($intId);

        if ($dc) {
            $dc->activeRecord->tstamp    = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_quiz_evaluation']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_quiz_evaluation']['config']['onsubmit_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }
}