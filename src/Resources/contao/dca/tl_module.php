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
$dca['palettes']['quiz'] = '{title_legend},name,headline,type;' . '{template_legend:hide},customTpl;' . '{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';


/**
 * Fields
 */

$dca['fields'] += $fields;