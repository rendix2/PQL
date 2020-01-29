<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 29. 1. 2020
 * Time: 17:00
 */

namespace pql\QueryPrinter;

use pql\Query;

/**
 * Class UpdateSelect
 *
 * @package pql\QueryPrinter
 * @author  Tomáš Babický tomas.babicky@websta.de
 */
class UpdateSelect implements IQueryPrinter
{
    /**
     * @var Query $query
     */
    private $query;

    /**
     * UpdateSelect constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * UpdateSelect destructor.
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
        $update = 'UPDATE ' . $this->query->getTable()->getName();

        $selectQueryPrinter = new Select($this->query->getUpdateData());

        return $update . $selectQueryPrinter->printQuery();
    }
}
