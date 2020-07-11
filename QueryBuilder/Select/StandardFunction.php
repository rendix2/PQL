<?php

namespace pql\QueryBuilder\Select;

class StandardFunction implements IExpression
{
    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $column
     */
    private $column;

    /**
     * @var array $params
     */
    private $params;

    /**
     * PqlFunction constructor.
     *
     * @param string $name
     * @param string $column
     * @param mixed ...$params
     */
    public function __construct($name, $column, ...$params)
    {
        $this->name = $name;
        $this->column = $column;
        $this->params = $params;
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        return $this->name . "('" . implode("', '", $this->params) . "')";
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return mixed[]
     */
    public function getParams()
    {
        return $this->params;
    }
}