<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Backend;

use Contao\Backend;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;

class Module extends Backend
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        parent::__construct();
        $this->framework = $framework;
    }

    public function getQuizArchives()
    {
        $quizArchives = [];
        $quizzes = System::getContainer()->get('huh.quiz.manager')->findAll([]);

        if (null === $quizzes) {
            return $quizArchives;
        }

        foreach ($quizzes as $quiz) {
            $quizArchives[$quiz->id] = $quiz->title;
        }

        return $quizArchives;
    }
}
