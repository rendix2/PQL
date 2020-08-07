<?php

namespace pql\QueryBuilder\Select\AggregateFunctions;

use pql\QueryBuilder\Select\AggregateFunction;

/**
 * Class Count
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select\AggregateFunctions
 */
class Count extends AggregateFunction
{
    /**
     * Count constructor.
     *
     * @param string $column
     */
    public function __construct($column)
    {
        parent::__construct(self::COUNT, $column);
    }
}
