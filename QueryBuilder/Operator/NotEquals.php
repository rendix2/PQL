<?php

namespace pql\QueryBuilder\Operator;

use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Class NotEquals
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Operator
 */
class NotEquals implements IOperator
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
        return '!==';
    }

    /**
     * @inheritDoc
     */
    public function checkConditions(ISelectExpression $column, ISelectExpression $value)
    {
        return true;
    }
}
