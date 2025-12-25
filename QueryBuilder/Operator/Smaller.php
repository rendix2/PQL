<?php

namespace pql\QueryBuilder\Operator;

use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Class Smaller
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Operator
 */
class Smaller implements IOperator
{
    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->evaluate();
    }

    /**
     * @inheritDoc
     */
    public function evaluate(): string
    {
        return '<';
    }

    /**
     * @inheritDoc
     */
    public function checkConditions(ISelectExpression $column, ISelectExpression $value): bool
    {
        return true;
    }
}
