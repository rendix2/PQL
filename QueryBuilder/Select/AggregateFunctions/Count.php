<?php

namespace pql\QueryBuilder\Select\AggregateFunctions;

use pql\QueryBuilder\Select\AggregateFunction;

class Count extends AggregateFunction
{
    public function __construct(string $column)
    {
        parent::__construct(self::COUNT, $column);
    }
}
