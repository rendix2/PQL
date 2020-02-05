<?php

namespace pql;

use Exception;
use pql\QueryBuilder\Query;

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
        if ($this->column instanceof Query) {
            $column = '(<br><br>' . (string)$this->column . '<br><br>)';
        } elseif (is_array($this->column)) {
            $column = '(' . implode(self::IN_SEPARATOR, $this->column) . ')';
        } else {
            $column = $this->column;
        }

        if ($this->value instanceof Query) {
            $value = '(<br><br>' . (string)$this->value . '<br><br>)';
        } elseif (is_array($this->value)) {
            $value =  '(' . implode(self::IN_SEPARATOR, $this->value) . ')';
        } else {
            $value = $this->value;
        }

        return $column . ' ' . mb_strtoupper($this->operator) . ' ' . $value;
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
