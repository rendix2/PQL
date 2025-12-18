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
    /**
     * @var QueryBuilder $query
     */
    private $query;

    /**
     * Query constructor.
     *
     * @param QueryBuilder $query
     */
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function evaluate()
    {
        $printer = new QueryPrinter($this->query);
        return $printer->printQuery();
    }

    /**
     * @return QueryBuilder
     */
    public function getQuery()
    {
        return $this->query;
    }
}
