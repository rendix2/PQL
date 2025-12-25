<?php

namespace pql\QueryBuilder\Select;

use pql\QueryBuilder\Query as QueryBuilder;
use pql\QueryPrinter\QueryPrinter;

/**
 * Class Query
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select
 */
class QueryExpression implements ISelectExpression
{
    private QueryBuilder $query;

    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }


    public function evaluate()
    {
        $printer = new QueryPrinter($this->query);
        return $printer->printQuery();
    }


    public function getQuery()
    {
        return $this->query;
    }
}
