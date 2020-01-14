<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 1. 2020
 * Time: 16:22
 */

namespace pql\QueryPrinter;

use pql\Query;

/**
 * Class Explain
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryPrinter
 * @internal
 */
class Explain implements IQueryPrinter
{
    /**
     * @var Query $query
     */
    private $query;

    /**
     * Explain constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Explain destructor.
     */
    public function __destruct()
    {
        $this->query = null;
    }

    /**
     * @inheritDoc
     */
    public function printQuery()
    {
        $selectQuery = new Select($this->query);

        return 'EXPLAIN ' . $selectQuery->printQuery();
    }
}
