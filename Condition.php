<?php

namespace pql;

use Exception;
use pql\QueryBuilder\Operator\IOperator;
use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Class Condition
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class Condition
{
    const string IN_SEPARATOR = ', ';

    private ISelectExpression $column;

    private IOperator $operator;

    private ISelectExpression $value;

    /**
     * Condition constructor.
     *
     * @param ISelectExpression $column
     * @param IOperator         $operator
     * @param ISelectExpression $value
     *
     * @throws Exception
     */
    public function __construct(ISelectExpression $column, IOperator $operator, ISelectExpression $value)
    {
        $this->column   = $column;
        $this->operator = $operator;
        $this->value    = $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->column->evaluate() . ' ' . $this->operator . ' ' . $this->value->evaluate();
    }

    /**
     * @return ISelectExpression
     */
    public function getColumn(): ISelectExpression
    {
        return $this->column;
    }

    /**
     * @return IOperator
     */
    public function getOperator(): IOperator
    {
        return $this->operator;
    }

    /**
     * @return ISelectExpression
     */
    public function getValue(): ISelectExpression
    {
        return $this->value;
    }
}
