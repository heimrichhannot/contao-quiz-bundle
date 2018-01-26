<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Manager;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\QuizBundle\Model\QuizQuestionModel;

class QuizQuestionManager
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
     * @return QuizQuestionModel|null
     */
    public function findOneBy($column, $value, array $options = [])
    {
        /** @var QuizQuestionModel $adapter */
        $adapter = $this->framework->getAdapter(QuizQuestionModel::class);

        return $adapter->findOneBy($column, $value, $options);
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|QuizQuestionModel|null
     */
    public function findBy($column, $value, array $options = [])
    {
        /** @var QuizQuestionModel $adapter */
        $adapter = $this->framework->getAdapter(QuizQuestionModel::class);

        return $adapter->findBy($column, $value, $options);
    }

    /**
     * Find published questions items by their parent ID
     *
     * @param integer $intId      The quiz ID
     * @param integer $intLimit   An optional limit
     * @param array   $arrOptions An optional options array
     *
     * @return \Model\Collection|QuizQuestionModel[]|QuizQuestionModel|null A collection of models or null if there are no news
     */
    public function findPublishedByPid($intId, $intLimit = 0, array $arrOptions = [])
    {
        /** @var QuizQuestionModel $adapter */
        $adapter = $this->framework->getAdapter(QuizQuestionModel::class);

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

    /**
     * Find published questions items by their parent ID which are not in array
     *
     * @param integer $intId      The quiz ID
     * @param integer $intLimit   An optional limit
     * @param array   $arrOptions An optional options array
     * @param array   $notIn
     *
     * @return \Model\Collection|QuizQuestionModel[]|QuizQuestionModel|null A collection of models or null if there are no news
     */
    public function findOnePublishedByPidNotInQuestions($intId, $notIn, $intLimit = 0, array $arrOptions = [])
    {
        /** @var QuizQuestionModel $adapter */
        $adapter = $this->framework->getAdapter(QuizQuestionModel::class);

        $t          = $adapter->getTable();
        $arrColumns = ["$t.pid=?"];

        if (!$this->isPreviewMode($arrOptions)) {
            $time         = \Date::floorToMinute();
            $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
        }

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$t.dateAdded DESC";
        }

        if (!empty($notIn)) {
            $ids          = implode(', ', $notIn);
            $arrColumns[] = "$t.id NOT IN ($ids)";
        }

        if ($intLimit > 0) {
            $arrOptions['limit'] = $intLimit;
        }

        return $adapter->findOneBy($arrColumns, $intId, $arrOptions);
    }

    /**
     * Find one published questions items by their parent ID
     *
     * @param integer $intId      The quiz ID
     * @param integer $intLimit   An optional limit
     * @param array   $arrOptions An optional options array
     *
     * @return \Model\Collection|QuizQuestionModel[]|QuizQuestionModel|null A collection of models or null if there are no news
     */
    public function findOnePublishedByPid($intId, $intLimit = 0, array $arrOptions = [])
    {
        /** @var QuizQuestionModel $adapter */
        $adapter = $this->framework->getAdapter(QuizQuestionModel::class);

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

        return $adapter->findOneBy($arrColumns, $intId, $arrOptions);
    }


    /**
     * @param       $intId
     * @param int   $intLimit
     * @param array $arrOptions
     *
     * @return int
     */
    public function countPublishedByPid($intId, $intLimit = 0, array $arrOptions = [])
    {
        /** @var QuizQuestionModel $adapter */
        $adapter = $this->framework->getAdapter(QuizQuestionModel::class);

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

        $collection = $adapter->findBy($arrColumns, $intId, $arrOptions);

        if (null == $collection) {
            return 0;
        }

        return $collection->count();
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $varId      The ID or alias
     * @param array $arrOptions An optional options array
     *
     * @return \Contao\Model\Collection|QuizQuestionModel|null
     */
    public function findByIdOrAlias($value, array $options = [])
    {
        /** @var QuizQuestionModel $adapter */
        $adapter = $this->framework->getAdapter(QuizQuestionModel::class);

        return $adapter->findByIdOrAlias($value, $options);
    }
}