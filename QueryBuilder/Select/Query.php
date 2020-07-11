<?php

namespace pql\QueryBuilder\Select;

use pql\QueryBuilder\Select;
use pql\QueryPrinter\QueryPrinter;

class Query implements IExpression
{
    /**
     * @var Select $query
     */
    private $query;

    /**
     * Query constructor.
     *
     * @param Select $query
     */
    public function __construct(Select $query)
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function evaluate()
    {
        $printer = new QueryPrinter($this->query);
        return $printer->printQuery();
    }
}