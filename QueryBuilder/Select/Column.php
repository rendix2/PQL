<?php

namespace pql\QueryBuilder\Select;

class Column implements ISelectExpression
{
    private string $column;

    public function __construct(string $column)
    {
        $this->column = $column;
    }

    public function evaluate()
    {
        return $this->column;
    }
}
