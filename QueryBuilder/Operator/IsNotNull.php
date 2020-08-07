<?php

namespace pql\QueryBuilder\Operator;

use pql\QueryBuilder\Select\Column;
use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Class IsNotNull
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Operator
 */
class IsNotNull implements IOperator
{
    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return 'is not null';
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        return 'is_not_null';
    }

    /**
     * @inheritDoc
     */
    public function checkConditions(ISelectExpression $column, ISelectExpression $value)
    {
        if ($column instanceof Column || $value instanceof Column) {
            return true;
        }

        return false;
    }
}
