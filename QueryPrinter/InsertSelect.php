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
 * Class InsertSelect
 *
 * @package pql\QueryPrinter
 * @author  rendix2 <rendix2@seznam.cz>
 * @internal
 */
class InsertSelect implements IQueryPrinter
{
    /**
     * @var Query $query
     */
    private $query;

    /**
     * InsertSelect constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * InsertSelect destructor.
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
        $insert = 'INSERT INTO ' .  $this->query->getTable()->getName();

        $selectQueryPrinter = new Select($this->query->getInsertData());

        return $insert . $selectQueryPrinter->printQuery();
    }
}
