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
];

$arrDca['fields'] = array_merge($arrDca['fields'], $fields);