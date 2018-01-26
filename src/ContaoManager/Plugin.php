<?php
/**
 * Created by PhpStorm.
 * User: kwagner
 * Date: 19.01.18
 * Time: 09:04
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\ContaoManager;


use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use HeimrichHannot\QuizBundle\HeimrichHannotContaoQuizBundle;

class Plugin implements BundlePluginInterface
{
    /**
     * @param ParserInterface $parser
     *
     * @return array
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeimrichHannotContaoQuizBundle::class)->setLoadAfter([ContaoCoreBundle::class, 'submissions_creator', 'submissions']),
        ];
    }

}