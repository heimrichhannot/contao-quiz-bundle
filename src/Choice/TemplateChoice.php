<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class TemplateChoice extends AbstractChoice
{
    /**
     * @return array
     */
    public function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.quiz');

        if (!isset($config['quiz']['templates'])) {
            return $choices;
        }

        $templates = $config['quiz']['templates'];

        foreach ($templates as $config) {
            $choices[$config['name']] = $config['template'];
        }

        return $choices;
    }
}
