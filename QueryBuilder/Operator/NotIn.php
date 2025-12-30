<?php

namespace pql\QueryBuilder\Operator;

use pql\QueryBuilder\Select\ArrayValue;
use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Class NotIn
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Operator
 */
class NotIn implements IOperator
{
    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return 'not in';
    }

    /**
     * @inheritDoc
     */
    public function evaluate(): string
    {
        return 'not_in';
    }

    /**
     * @inheritDoc
     */
    public function checkConditions(ISelectExpression $column, ISelectExpression $value): bool
    {
        if ($column instanceof ArrayValue || $value instanceof ArrayValue) {
            return true;
        }
    }
}
