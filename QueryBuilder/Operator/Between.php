<?php

namespace pql\QueryBuilder\Operator;

use pql\QueryBuilder\Select\ArrayValue;
use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Class Between
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Operator
 */
class Between implements IOperator
{
    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->evaluate();
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        return 'between';
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
