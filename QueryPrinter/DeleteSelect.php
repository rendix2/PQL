<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 29. 1. 2020
 * Time: 16:53
 */

namespace pql\QueryPrinter;

use pql\Query;

/**
 * Class DeleteSelect
 *
 * @package pql\QueryPrinter
 * @author  Tomáš Babický tomas.babicky@websta.de
 */
class DeleteSelect implements IQueryPrinter
{
    /**
     * @var Query $query
     */
    private $query;

    /**
     * DeleteSelect constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query;
    }

    /**
     * DeleteSelect destructor.
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
        $delete = 'DELETE FROM ' . $this->query->getTable()->getName();

        $selectQueryPrinter = new Select($this->query->getDeleteData());

        return $delete . $selectQueryPrinter->printQuery();
    }
}
