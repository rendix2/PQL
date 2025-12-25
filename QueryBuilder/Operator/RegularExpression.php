<?php

namespace pql\QueryBuilder\Operator;

use pql\QueryBuilder\Select\ISelectExpression;
use pql\QueryBuilder\Select\ValueExpression;

/**
 * Class RegularExpression
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Operator
 */
class RegularExpression implements IOperator
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
        return 'regex';
    }

    /**
     * @inheritDoc
     */
    public function checkConditions(ISelectExpression $column, ISelectExpression $value): bool
    {
        if ($column instanceof ValueExpression || $value instanceof ValueExpression) {
            return true;
        }

        return false;
    }
}
