<?php

namespace pql\QueryBuilder\Select\AggregateFunctions;

use pql\QueryBuilder\Select\AggregateFunction;

class Average extends AggregateFunction
{
    public function __construct(string $column)
    {
        parent::__construct(self::AVERAGE, $column);
    }
}
