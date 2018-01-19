<?php

\Contao\Controller::loadDataContainer('tl_module');
\Contao\System::loadLanguageFile('tl_module');

$GLOBALS['TL_DCA']['tl_quiz_question'] = [
    'config'   => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_quiz',
        'ctable'            => ['tl_content', 'tl_quiz_answer'],
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
    'list'     => [
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
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_quiz']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_quiz']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_quiz']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_quiz']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['addImage', 'overwriteMeta'],
        'default'      => '{general_legend},title,author;{question_legend},question;{image_legend},addImage;{expert_legend:hide},cssClass;{publish_legend},published,start,stop',
    ],

    'subpalettes' => [
        'addImage'      => 'singleSRC,size,floating,imagemargin,fullsize,overwriteMeta',
        'overwriteMeta' => 'alt,imageTitle,imageUrl,caption',
    ],
    'fields'      => [
        'id'            => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'           => [
            'foreignKey' => 'tl_quit.id',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp'        => [
            'label' => &$GLOBALS['TL_LANG']['tl_quiz_question']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded'     => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_question']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'published'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_question']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'start'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_question']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'stop'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_question']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'author'        => [
            'label'      => &$GLOBALS['TL_LANG']['tl_quiz_question']['author'],
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
        'cssClass'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_question']['cssClass'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'addImage'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_question']['addImage'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'overwriteMeta' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['overwriteMeta'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'singleSRC'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'mandatory' => true],
            'sql'       => "binary(16) NULL",
        ],
        'alt'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['alt'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'imageTitle'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['imageTitle'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'size'          => [
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
        'imagemargin'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['imagemargin'],
            'exclude'   => true,
            'inputType' => 'trbl',
            'options'   => $GLOBALS['TL_CSS_UNITS'],
            'eval'      => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'imageUrl'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['imageUrl'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'dcaPicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'caption'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['caption'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'allowHtml' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'floating'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['floating'],
            'default'   => 'above',
            'exclude'   => true,
            'inputType' => 'radioTable',
            'options'   => ['above', 'left', 'right', 'below'],
            'eval'      => ['cols' => 4, 'tl_class' => 'w50'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
        'fullsize'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['fullsize'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'question'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_quiz_question']['question'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql'       => "text NULL",
        ],
    ],
];