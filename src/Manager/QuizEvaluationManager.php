<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Manager;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\QuizBundle\Model\QuizEvaluationModel;

class QuizEvaluationManager
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
        $this->framework = $framework;
    }


    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return QuizEvaluationModel|null
     */
    public function findOneBy($column, $value, array $options = [])
    {
        /** @var QuizEvaluationModel $adapter */
        $adapter = $this->framework->getAdapter(QuizEvaluationModel::class);

        return $adapter->findOneBy($column, $value, $options);
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|QuizEvaluationModel|null
     */
    public function findBy($column, $value, array $options = [])
    {
        /** @var QuizEvaluationModel $adapter */
        $adapter = $this->framework->getAdapter(QuizEvaluationModel::class);

        return $adapter->findBy($column, $value, $options);
    }

    /**
     * Find published answers items by their parent ID
     *
     * @param integer $intId      The question ID
     * @param integer $intLimit   An optional limit
     * @param array   $arrOptions An optional options array
     *
     * @return \Model\Collection|QuizEvaluationModel[]|QuizEvaluationModel|null A collection of models or null if there are no news
     */
    public function findByPid($intId, $intLimit = 0, array $arrOptions = [])
    {
        /** @var QuizEvaluationModel $adapter */
        $adapter = $this->framework->getAdapter(QuizEvaluationModel::class);

        $t          = $adapter->getTable();
        $arrColumns = ["$t.pid=?"];

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$t.dateAdded DESC";
        }

        if ($intLimit > 0) {
            $arrOptions['limit'] = $intLimit;
        }

        return $adapter->findBy($arrColumns, $intId, $arrOptions);
    }

    /**
     * Find one published answers items by their parent ID
     *
     * @param integer $intId      The question ID
     * @param integer $intLimit   An optional limit
     * @param array   $arrOptions An optional options array
     *
     * @return \Model\Collection|QuizEvaluationModel[]|QuizEvaluationModel|null A collection of models or null if there are no news
     */
    public function findOneByPid($intId, $intLimit = 0, array $arrOptions = [])
    {
        /** @var QuizEvaluationModel $adapter */
        $adapter = $this->framework->getAdapter(QuizEvaluationModel::class);

        $t          = $adapter->getTable();
        $arrColumns = ["$t.pid=?"];

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$t.dateAdded DESC";
        }

        if ($intLimit > 0) {
            $arrOptions['limit'] = $intLimit;
        }

        return $adapter->findOneBy($arrColumns, $intId, $arrOptions);
    }

    /**
     * Find published questions items by their parent ID
     *
     * @param integer $intId      The quiz ID
     * @param integer $intLimit   An optional limit
     * @param array   $arrOptions An optional options array
     *
     * @return \Model\Collection|QuizEvaluationModel[]|QuizEvaluationModel|null A collection of models or null if there are no news
     */
    public function findPublishedByPid($intId, $intLimit = 0, array $arrOptions = [])
    {
        /** @var QuizEvaluationModel $adapter */
        $adapter = $this->framework->getAdapter(QuizEvaluationModel::class);

        $t          = $adapter->getTable();
        $arrColumns = ["$t.pid=?"];

        if (!$this->isPreviewMode($arrOptions)) {
            $time         = \Date::floorToMinute();
            $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
        }

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$t.dateAdded DESC";
        }

        if ($intLimit > 0) {
            $arrOptions['limit'] = $intLimit;
        }

        return $adapter->findBy($arrColumns, $intId, $arrOptions);
    }

    public function isPreviewMode($arrOptions)
    {
        if (isset($arrOptions['ignoreFePreview'])) {
            return false;
        }

        return \defined('BE_USER_LOGGED_IN') && true === BE_USER_LOGGED_IN;
    }
}