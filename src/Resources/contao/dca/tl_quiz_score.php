<?php

$GLOBALS['TL_DCA']['tl_quiz_score'] = [
    'config' => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_quiz',
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
    'fields' => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'       => [
            'foreignKey' => 'tl_quiz.id',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'score'     => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];
