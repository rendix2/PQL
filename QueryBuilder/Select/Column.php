<?php

namespace pql\QueryBuilder\Select;

class Column implements IExpression
{
    /**
     * @var string $column
     */
    private $column;

    /**
     * Column constructor.
     *
     * @param $column
     */
    public function __construct($column)
    {
        $this->column = $column;
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        return $this->column;
    }
}