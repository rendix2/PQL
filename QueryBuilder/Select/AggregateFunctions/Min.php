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
    public function __construct(string $column)
    {
        parent::__construct(self::MIN, $column);
    }
}
