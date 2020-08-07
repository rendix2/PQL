<?php

namespace pql\QueryExecutor\AggregateFunctions;

use pql\QueryExecutor\SelectQuery;

/**
 * Class AggregateFunction
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecutor\AggregateFunctions
 */
abstract class AggregateFunction implements IAggregateFunction
{
    /**
     * @var SelectQuery $query
     */
    private $query;

    /**
     * AggregateFunction constructor.
     *
     * @param SelectQuery $query
     */
    public function __construct(SelectQuery $query)
    {
        $this->query = $query;
    }

    /**
     * @return SelectQuery
     */
    public function getQuery()
    {
        return $this->query;
    }
}
