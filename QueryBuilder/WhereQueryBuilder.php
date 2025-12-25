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
    private array $whereConditions;

    private bool $hasWhereCondition;

    public function where(ISelectExpression $column, IOperator $operator, ISelectExpression $value): SelectQuery
    {
        if (!$operator->checkConditions($column, $value)) {
            throw new Exception('Given parameters are not good....');
        }

        $condition = new Condition($column, $operator, $value);

        $this->whereConditions[] = $condition;
        $this->hasWhereCondition = true;

        return $this;
    }

    public function getWhereConditions(): array
    {
        return $this->whereConditions;
    }

    public function hasWhereCondition(): bool
    {
        return $this->hasWhereCondition;
    }

    public function removeWhereCondition(string $key): void
    {
        unset($this->whereConditions[$key]);

        $this->whereConditions = array_values($this->whereConditions);
        $this->hasWhereCondition = count($this->whereConditions);
    }
}
