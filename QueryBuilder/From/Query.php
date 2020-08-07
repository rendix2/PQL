<?php

namespace pql\QueryBuilder\From;

use pql\QueryBuilder\Query as QueryBuilder;
use pql\QueryPrinter\QueryPrinter;

class Query implements IFromExpression
{
    /**
     * @var QueryBuilder $query
     */
    private $query;

    /**
     * Query constructor.
     *
     * @param QueryBuilder $query
     */
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * @return QueryBuilder
     */
    public function getQuery()
    {
        return $this->query;
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