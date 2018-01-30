<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle;

use HeimrichHannot\QuizBundle\DependencyInjection\HeimrichHannotContaoQuizExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoQuizBundle extends Bundle
{
    /**
     * @return HeimrichHannotContaoQuizExtension
     */
    public function getContainerExtension()
    {
        return new HeimrichHannotContaoQuizExtension();
    }
}
