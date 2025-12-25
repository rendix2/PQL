<?php

namespace pql\QueryBuilder\Select;

/**
 * Class StandardFunction
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select
 */
class StandardFunction implements ISelectExpression
{
    /**
     * @var string $name
     */
    private string $name;

    /**
     * @var string $column
     */
    private string $column;

    /**
     * @var array $params
     */
    private array $params;

    public function __construct(string$name, string $column, mixed ...$params)
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
