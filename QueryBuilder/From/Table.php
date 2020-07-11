<?php

namespace pql\QueryBuilder\From;

class Table implements IExpression
{
    private $table;

    public function __construct(\pql\Table $table)
    {
        $this->table = $table;
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        return $this->table->getName();
    }
}