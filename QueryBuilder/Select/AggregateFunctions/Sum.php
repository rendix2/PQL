<?php

namespace pql\QueryBuilder\Select\AggregateFunctions;

use pql\QueryBuilder\Select\AggregateFunction;

/**
 * Class Sum
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select\AggregateFunctions
 */
class Sum extends AggregateFunction
{
    /**
     * Sum constructor.
     *
     * @param string $column
     */
    public function __construct($column)
    {
        parent::__construct(self::SUM, $column);
    }
}
