<?php
/**
 * Created by PhpStorm.
 * User: kwagner
 * Date: 19.01.18
 * Time: 08:59
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