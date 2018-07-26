<?php

$dca = &$GLOBALS['TL_DCA']['tl_user'];

/**
 * Palettes
 */
$dca['palettes']['extend'] = str_replace('fop;', 'fop;{quiz_legend},quizs,quizp;', $dca['palettes']['extend']);
$dca['palettes']['custom'] = str_replace('fop;', 'fop;{quiz_legend},quizs,quizp;', $dca['palettes']['custom']);

/**
 * Fields
 */
$dca['fields']['quizs'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_user']['quizs'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_quiz.title',
    'eval'       => ['multiple' => true],
    'sql'        => "blob NULL"
];

$dca['fields']['quizp'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['quizp'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL"
];

