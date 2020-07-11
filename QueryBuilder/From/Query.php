<?php

namespace pql\QueryBuilder\From;

use pql\QueryBuilder\Select;
use pql\QueryPrinter\QueryPrinter;

class Query implements IExpression
{
    /**
     * @var Select
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
     * @inheritDoc
     */
    public function evaluate()
    {
        $printer = new QueryPrinter($this->query);

        return $printer->printQuery();
    }
}