<?php

/**
 * Class Condition
 */
class Condition
{
    /**
     * @var
     */
    private $column;

    /**
     * @var
     */
    private $operator;

    /**
     * @var
     */
    private $value;

    /**
     * Condition constructor.
     * @param string|array|Query|FunctionPql $column
     * @param string                         $operator
     * @param string|array|Query|FunctionPql $value
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
     *
     */
    public function __destruct()
    {
        $this->column = null;
        $this->operator = null;
        $this->value = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->column . ' ' . $this->operator . ' ' .  $this->value;
    }

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return mixed
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