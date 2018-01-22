<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\QuizBundle\Choice;

use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class TemplateChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = \System::getContainer()->getParameter('huh.quiz');

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
