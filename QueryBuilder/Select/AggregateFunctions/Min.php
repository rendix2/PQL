<?php

namespace pql\QueryBuilder\Select\AggregateFunctions;

use pql\QueryBuilder\Select\AggregateFunction;

/**
 * Class Min
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select\AggregateFunctions
 */
class Min extends AggregateFunction
{
    /**
     * Min constructor.
     *
     * @param string $column
     */
    public function __construct($column)
    {
        parent::__construct(self::MIN, $column);
    }
}
