<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Backend;


class Module extends \Backend
{
    /**
     * returns all modules with type submission reader
     *
     * @param \DataContainer $dc
     *
     * @return array
     */
    public function getSubmissionModules(\DataContainer $dc)
    {
        return static::getModuleOptions('submission_reader');
    }

    protected static function getModuleOptions($strType)
    {
        $arrOptions = [];

        $objModules = \ModuleModel::findByType($strType);

        if ($objModules === null) {
            return $arrOptions;
        }

        return $objModules->fetchEach('name');
    }
}