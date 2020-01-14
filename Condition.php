<?php

namespace pql;

use Exception;

/**
 * Class Condition
 *
 * @author rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class Condition
{
    /**
     * @var string|array|Query|AggregateFunction $column
     */
    private $column;

    /**
     * @var string $operator
     */
    private $operator;

    /**
     * @var string|array|Query|AggregateFunction $value
     */
    private $value;

    /**
     * Condition constructor.
     *
     * @param string|array|Query|AggregateFunction $column
     * @param string                               $operator
     * @param string|array|Query|AggregateFunction $value
     *
     * @throws Exception
     */
    public function __construct($column, $operator, $value)
    {
        if (!Operator::isOperatorValid($operator)) {
            throw new Exception(sprintf('Unknown operator "%s".', $operator));
        }

        $this->column = $column;
        $this->operator = mb_strtolower($operator);
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
        return $this->column . ' ' . $this->operator . ' ' .  $this->value;
    }

    /**
     * @return string|array|Query|AggregateFunction
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return string|array|Query|AggregateFunction
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
