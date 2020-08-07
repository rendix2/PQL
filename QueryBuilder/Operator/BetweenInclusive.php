<?php

namespace pql\QueryBuilder\Operator;

use pql\QueryBuilder\Select\ArrayValue;
use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Class BetweenInclusive
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Operator
 */
class BetweenInclusive implements IOperator
{
    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return 'between inclusive';
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        return 'between_inclusive';
    }

    /**
     * @inheritDoc
     */
    public function checkConditions(ISelectExpression $column, ISelectExpression $value)
    {
        if ($column instanceof ArrayValue && count($column->getValues()) === 2) {
            return true;
        }

        if ($value instanceof ArrayValue && count($value->getValues()) === 2) {
            return true;
        }

        return false;
    }
}
