<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:47
 */

namespace pql\QueryBuilder;

use Exception;
use pql\Condition;
use pql\QueryBuilder\Operator\IOperator;
use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Trait Where
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder
 */
trait WhereQueryBuilder
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
     * @param ISelectExpression $column
     * @param IOperator         $operator
     * @param ISelectExpression $value
     *
     * @return WhereQueryBuilder|SelectQuery
     * @throws Exception
     */
    public function where(ISelectExpression $column, IOperator $operator, ISelectExpression $value)
    {
        if (!$operator->checkConditions($column, $value)) {
            throw new Exception('Given parameters are not good....');
        }

        $condition = new Condition($column, $operator, $value);

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
