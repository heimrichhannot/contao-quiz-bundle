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
$dca['palettes']['quiz'] = '{title_legend},name,headline,type;' . '{config_legend},quiz;' . '{template_legend:hide},customTpl;' . '{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

/**
 * Fields
 */
$fields = [
    'quiz' => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['quiz'],
        'exclude'    => true,
        'filter'     => true,
        'inputType'  => 'select',
        'foreignKey' => 'tl_quiz.title',
        'eval'       => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
        'sql'        => "int(10) unsigned NOT NULL default '0'",
    ],
];

$dca['fields'] += $fields;