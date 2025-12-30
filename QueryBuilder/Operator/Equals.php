<?php

namespace pql\QueryBuilder\Operator;

use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Class Equals
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Operator
 */
class Equals implements IOperator
{
    public function __toString(): string
    {
        return $this->evaluate();
    }

    public function evaluate(): string
    {
        return '=';
    }

    public function checkConditions(ISelectExpression $column, ISelectExpression $value): bool
    {
        return true;
    }
}
