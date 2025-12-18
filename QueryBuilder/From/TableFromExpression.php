<?php

namespace pql\QueryBuilder\From;

class TableFromExpression implements IFromExpression
{
    /**
     * @var string $table
     */
    private $table;

    /**
     * Table constructor.
     * @param string $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        return $this->table;
    }
}