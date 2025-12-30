<?php

namespace pql\QueryBuilder\Operator;

use pql\QueryBuilder\Select\ISelectExpression;
use pql\QueryBuilder\Select\QueryExpression;

/**
 * Class Exists
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Operator
 */
class Exists implements IOperator
{
    public function __toString(): string
    {
        return $this->evaluate();
    }

    public function evaluate(): string
    {
        return 'exists';
    }

    /**
     * @inheritDoc
     */
    public function checkConditions(ISelectExpression $column, ISelectExpression $value): bool
    {
        if ($column instanceof QueryExpression || $value instanceof QueryExpression) {
            return true;
        }

        return false;
    }
}
