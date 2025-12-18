<?php

namespace pql\QueryExecutor\AggregateFunctions;

use pql\QueryExecutor\SelectExecutor;

/**
 * Class AggregateFunction
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecutor\AggregateFunctions
 */
abstract class AbstractAggregateFunction implements IAggregateFunction
{
    /**
     * @var SelectExecutor $query
     */
    private $query;

    /**
     * AggregateFunction constructor.
     *
     * @param SelectExecutor $query
     */
    public function __construct(SelectExecutor $query)
    {
        $this->query = $query;
    }

    /**
     * @return SelectExecutor
     */
    public function getQuery()
    {
        return $this->query;
    }
}
