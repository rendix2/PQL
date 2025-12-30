<?php

namespace pql\QueryExecutor\AggregateFunctions;

interface IAggregateFunction
{

    public function run(string $column, string $functionColumnName): void;
}