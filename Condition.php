<?php

namespace pql;

use Exception;
use pql\QueryBuilder\Operator\IOperator;
use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Class Condition
 *
 * @author rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class Condition
{
    /**
     * @var string
     */
    const IN_SEPARATOR = ', ';

    /**
     * @var ISelectExpression $column
     */
    private $column;

    /**
     * @var IOperator $operator
     */
    private $operator;

    /**
     * @var ISelectExpression $value
     */
    private $value;

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
        /*
        if (!Operator::isOperatorValid($operator)) {
            throw new Exception(sprintf('Unknown operator "%s".', $operator));
        }
        */

        $this->column = $column;
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * Condition destructor.
     */
    public function __destruct()
    {
        $this->column   = null;
        $this->operator = null;
        $this->value    = null;
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
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return IOperator
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return ISelectExpression
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * inversed Condition
     *
     * @return Condition
     */
    public function inverse()
    {
        return new Condition($this->value, $this->operator, $this->column);
    }
}
