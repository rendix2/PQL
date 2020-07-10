<?php

namespace pql\QueryExecutor\AggregateFunctions;

/**
 * Interface IAggregateFunction
 *
 * @package pql\QueryExecutor\AggregateFunctions
 */
interface IAggregateFunction
{

    /**
     * @param string $column
     * @param string $functionColumnName
     *
     * @return void
     */
    public function run($column, $functionColumnName);
}