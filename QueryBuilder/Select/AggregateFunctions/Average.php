<?php

namespace pql\QueryBuilder\Select\AggregateFunctions;

use pql\QueryBuilder\Select\AggregateFunction;

/**
 * Class Average
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select\AggregateFunctions
 */
class Average extends AggregateFunction
{
    /**
     * Average constructor.
     *
     * @param string $column
     */
    public function __construct($column)
    {
        parent::__construct(self::AVERAGE, $column);
    }
}
