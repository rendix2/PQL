<?php

namespace pql\QueryBuilder\Operator;

use pql\QueryBuilder\Select\ISelectExpression;
use pql\QueryBuilder\Select\Value;

/**
 * Class Larger
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Operator
 */
class Larger implements IOperator
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
        return '>';
    }

    /**
     * @inheritDoc
     */
    public function checkConditions(ISelectExpression $column, ISelectExpression $value)
    {
        if ($column instanceof Value || $value instanceof Value) {
            return true;
        }

        return false;
    }
}
