<?php

namespace pql\QueryBuilder\Operator;

use pql\QueryBuilder\Select\ArrayValue;
use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Class In
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Operator
 */
class In implements IOperator
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
        return 'in';
    }

    /**
     * @inheritDoc
     */
    public function checkConditions(ISelectExpression $column, ISelectExpression $value)
    {
       if ($column instanceof ArrayValue || $value instanceof ArrayValue) {
           return true;
       }

       return false;
    }
}
