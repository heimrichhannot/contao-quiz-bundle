<?php

$dca = &$GLOBALS['TL_DCA']['tl_user_group'];

/**
 * Palettes
 */
$dca['palettes']['default'] = str_replace('fop;', 'fop;{quiz_legend},quizs,quizp;', $dca['palettes']['default']);

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