<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 29. 1. 2020
 * Time: 17:00
 */

namespace pql\QueryPrinter;

use pql\QueryBuilder\UpdateSelect as UpdateSelectBuilder;

/**
 * Class UpdateSelect
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryPrinter
 */
class UpdateSelect implements IQueryPrinter
{
    /**
     * @var UpdateSelectBuilder $query
     */
    private $query;

    /**
     * UpdateSelect constructor.
     *
     * @param UpdateSelectBuilder $query
     */
    public function __construct(UpdateSelectBuilder $query)
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

        $selectQueryPrinter = new Select($this->query->getData());

        return $update . $selectQueryPrinter->printQuery();
    }
}
