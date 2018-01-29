<?php

\Controller::loadDataContainer('tl_submission');

$arrDca = &$GLOBALS['TL_DCA']['tl_submission'];

/**
 * Fields
 */
$fields = [
    'quizScore' => [
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
    'quizToken' => [
        'sql' => "varchar(255) NOT NULL default ''",
    ],
];

$arrDca['fields'] = array_merge($arrDca['fields'], $fields);