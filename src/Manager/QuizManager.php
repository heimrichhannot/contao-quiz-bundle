<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Manager;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\QuizBundle\Model\QuizModel;

class QuizManager
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
     * @return QuizModel|null
     */
    public function findOneBy($column, $value, array $options = [])
    {
        /** @var QuizModel $adapter */
        $adapter = $this->framework->getAdapter(QuizModel::class);

        return $adapter->findOneBy($column, $value, $options);
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $varId      The ID or alias
     * @param array $arrOptions An optional options array
     *
     * @return \Contao\Model\Collection|QuizModel|null
     */
    public function findByIdOrAlias($value, array $options = [])
    {
        /** @var QuizModel $adapter */
        $adapter = $this->framework->getAdapter(QuizModel::class);

        return $adapter->findByIdOrAlias($value, $options);
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|QuizModel|null
     */
    public function findBy($column, $value, array $options = [])
    {
        /** @var QuizModel $adapter */
        $adapter = $this->framework->getAdapter(QuizModel::class);

        return $adapter->findBy($column, $value, $options);
    }
}