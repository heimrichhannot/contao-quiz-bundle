<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use Contao\System;
use Haste\Util\Url;
use HeimrichHannot\QuizBundle\Entity\QuizSession;

abstract class Manager
{
    protected ContaoFramework $framework;

    /**
     * @var QuizSession
     */
    protected $session;

    /**
     * @var Model
     */
    protected $class;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
        $this->session = new QuizSession();
        $this->class = Model::class;
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     *
     * @return Model|null
     */
    public function findOneBy($column, $value, array $options = [])
    {
        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter($this->class);

        return $adapter->findOneBy($column, $value, $options);
    }

    /**
     * @return Model\Collection|null
     */
    public function findAll(array $options)
    {
        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter($this->class);

        return $adapter->findAll($options);
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     *
     * @return \Contao\Model\Collection|Model|null
     */
    public function findBy($column, $value, array $options = [])
    {
        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter($this->class);

        return $adapter->findBy($column, $value, $options);
    }

    /**
     * Find published answers items by their parent ID.
     *
     * @param int   $intId      The question ID
     * @param int   $intLimit   An optional limit
     * @param array $arrOptions An optional options array
     *
     * @return \Model\Collection|Model[]|Model|null A collection of models or null if there are no news
     */
    public function findPublishedByPid($intId, $intLimit = 0, array $arrOptions = [])
    {
        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter($this->class);

        $t = $adapter->getTable();
        $arrColumns = ["$t.pid=?"];

        if (!$this->isPreviewMode($arrOptions)) {
            $time = \Date::floorToMinute();
            $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
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
     * Find one published answers items by their parent ID.
     *
     * @param int   $intId      The question ID
     * @param int   $intLimit   An optional limit
     * @param array $arrOptions An optional options array
     *
     * @return \Model\Collection|Model[]|Model|null A collection of models or null if there are no news
     */
    public function findOnePublishedByPid($intId, $intLimit = 0, array $arrOptions = [])
    {
        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter($this->class);

        $t = $adapter->getTable();
        $arrColumns = ["$t.pid=?"];

        if (!$this->isPreviewMode($arrOptions)) {
            $time = \Date::floorToMinute();
            $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
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
     * @return string
     */
    public function getUri()
    {
        return $this->framework->getAdapter(Url::class)->removeQueryString(['question', 'answer'], System::getContainer()->get('request_stack')->getCurrentRequest()->getUri());
    }

    /**
     * @param $pid
     *
     * @return int
     */
    public function countByPid($pid, array $arrOptions = [])
    {
        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter($this->class);

        return $adapter->countBy('pid', $pid, $arrOptions);
    }

    /**
     * Find one published answers items by their parent ID.
     *
     * @param int   $intId      The question ID
     * @param int   $intLimit   An optional limit
     * @param array $arrOptions An optional options array
     *
     * @return \Model\Collection|Model[]|Model|null A collection of models or null if there are no news
     */
    public function findOneByPid($intId, $intLimit = 0, array $arrOptions = [])
    {
        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter($this->class);
        $t = $adapter->getTable();
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
     * Find published answers items by their parent ID.
     *
     * @param int   $intId      The question ID
     * @param int   $intLimit   An optional limit
     * @param array $arrOptions An optional options array
     *
     * @return \Model\Collection|Model[]|Model|null A collection of models or null if there are no news
     */
    public function findByPid($intId, $intLimit = 0, array $arrOptions = [])
    {
        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter($this->class);
        $t = $adapter->getTable();
        $arrColumns = ["$t.pid=?"];
        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$t.dateAdded DESC";
        }
        if ($intLimit > 0) {
            $arrOptions['limit'] = $intLimit;
        }

        return $adapter->findBy($arrColumns, $intId, $arrOptions);
    }
}
