<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dca = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Palettes
 */
$dca['palettes']['quiz']           = '{title_legend},name,headline,type;' . '{template_legend:hide},customTpl;' . '{quiz_legend},singleQuiz;' . '{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$dca['palettes']['quizSubmission'] = '{title_legend},name,headline,type;' . '{template_legend:hide},customTpl;' . '{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

/**
 * Subpalettes
 */
$dca['palettes']['__selector__'][] = 'singleQuiz';
$dca['subpalettes']['singleQuiz']  = 'quizArchive';

/**
 * fields
 */
$fields = [
    'singleQuiz'  => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['singleQuiz'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default '0'",
    ],
    'quizArchive' => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['quizArchive'],
        'inputType'        => 'select',
        'options_callback' => ['huh.quiz.module.manager', 'getQuizArchives'],
        'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true],
        'sql'              => "int(10) NOT NULL default '0'",
    ],
];

$dca['fields'] += $fields;