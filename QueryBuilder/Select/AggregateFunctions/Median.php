<?php

namespace pql\QueryBuilder\Select\AggregateFunctions;

use pql\QueryBuilder\Select\AggregateFunction;

/**
 * Class Median
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select\AggregateFunctions
 */
class Median extends AggregateFunction
{
    /**
     * Median constructor.
     *
     * @param string $column
     */
    public function __construct($column)
    {
        parent::__construct(self::MEDIAN, $column);
    }
}
