<?php

namespace pql\QueryBuilder\From;

class TableFromExpression implements IFromExpression
{
    private string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function evaluate(): string
    {
        return $this->table;
    }
}