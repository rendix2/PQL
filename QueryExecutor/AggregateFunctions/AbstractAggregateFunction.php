<?php

namespace pql\QueryExecutor\AggregateFunctions;

use pql\QueryExecutor\SelectExecutor;

abstract class AbstractAggregateFunction implements IAggregateFunction
{
    private SelectExecutor $query;

    public function __construct(SelectExecutor $query)
    {
        $this->query = $query;
    }

    public function getQuery(): SelectExecutor
    {
        return $this->query;
    }
}
