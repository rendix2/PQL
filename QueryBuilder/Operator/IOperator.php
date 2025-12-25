<?php

namespace pql\QueryBuilder\Operator;

use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Interface IOperator
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Operator
 */
interface IOperator
{
    public function __toString(): string;

    public function evaluate(): string;

    /**
     * check column and value if this operator can work with them
     *
     * @param ISelectExpression $column
     * @param ISelectExpression $value
     *
     * @return bool
     */
    public function checkConditions(ISelectExpression $column, ISelectExpression $value): bool;
}
