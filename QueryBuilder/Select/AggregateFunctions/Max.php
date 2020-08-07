<?php

namespace pql\QueryBuilder\Select\AggregateFunctions;

use pql\QueryBuilder\Select\AggregateFunction;

/**
 * Class Max
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select\AggregateFunctions
 */
class Max extends AggregateFunction
{
    /**
     * Max constructor.
     * @param string $column
     */
    public function __construct($column)
    {
        parent::__construct(self::MAX, $column);
    }
}
