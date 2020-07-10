<?php

namespace pql\QueryExecutor\AggregateFunctions;

use pql\QueryExecutor\Select;

/**
 * Class AggregateFunction
 *
 * @package pql\QueryExecutor\AggregateFunctions
 */
abstract class AggregateFunction implements IAggregateFunction
{
    /**
     * @var Select $query
     */
    private $query;

    /**
     * AggregateFunction constructor.
     *
     * @param Select $query
     */
    public function __construct(Select $query)
    {
        $this->query = $query;
    }

    /**
     * @return Select
     */
    public function getQuery()
    {
        return $this->query;
    }
}