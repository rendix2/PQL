<?php

namespace pql\QueryBuilder\Select;

use pql\QueryPrinter\QueryPrinter;

/**
 * Class Query
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select
 */
class Query implements ISelectExpression
{
    /**
     * @var \pql\QueryBuilder\Query $query
     */
    private $query;

    /**
     * Query constructor.
     *
     * @param \pql\QueryBuilder\Query $query
     */
    public function __construct(\pql\QueryBuilder\Query $query)
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
     * @return \pql\QueryBuilder\Query
     */
    public function getQuery()
    {
        return $this->query;
    }
}
