<?php

namespace pql\QueryBuilder\From;

use pql\QueryBuilder\Query as QueryBuilder;
use pql\QueryPrinter\QueryPrinter;

class QueryFromExpression implements IFromExpression
{
    private QueryBuilder $query;

    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }

    public function evaluate(): string
    {
        $printer = new QueryPrinter($this->query);

        return $printer->printQuery();
    }
}
