<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:47
 */

namespace pql\QueryBuilder;

use Exception;
use pql\AggregateFunction;
use pql\Condition;
use pql\Operator;

/**
 * Class Where
 *
 * @package pql\QueryBuilder
 * @author  rendix2 <rendix2@seznam.cz>
 */
trait Where
{
    /**
     * @var Condition[] $whereConditions
     */
    private $whereConditions;

    /**
     * @var bool $hasWhereCondition
     */
    private $hasWhereCondition;

    /**
     * @param string|int|AggregateFunction|Query $column
     * @param string                             $operator
     * @param string|int|AggregateFunction|Query $value
     *
     * @return Where|Select
     * @throws Exception
     */
    public function where($column, $operator, $value)
    {
        $condition = new Condition($column, $operator, $value);

        if ($condition->getOperator() === Operator::BETWEEN || $condition->getOperator() === Operator::BETWEEN_INCLUSIVE) {
            if (!is_array($condition->getValue()) && !is_array($condition->getColumn())) {
                throw new Exception('Parameter for between must be array.');
            }

            if (count($condition->getValue()) !== 2 && count($condition->getColumn()) !== 2) {
                throw new Exception('I need two parameters.');
            }
        }

        if ($condition->getOperator() === Operator::EXISTS) {
            if (!($condition->getColumn() instanceof self) && !$condition->getValue() instanceof self) {
                throw new Exception('Parameter for between must be Query.');
            }
        }

        $this->whereConditions[] = $condition;
        $this->hasWhereCondition = true;

        return $this;
    }

    /**
     * @return Condition[]
     */
    public function getWhereConditions()
    {
        return $this->whereConditions;
    }

    /**
     * @return bool
     */
    public function hasWhereCondition()
    {
        return $this->hasWhereCondition;
    }

    /**
     *
     * @param string $key
     * @internal
     */
    public function removeWhereCondition($key)
    {
        unset($this->whereConditions[$key]);

        $this->whereConditions = array_values($this->whereConditions);
        $this->hasWhereCondition = count($this->whereConditions);
    }
}
