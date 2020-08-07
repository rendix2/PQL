<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 29. 1. 2020
 * Time: 16:53
 */

namespace pql\QueryPrinter;

use pql\QueryBuilder\DeleteSelectQuery as DeleteSelectBuilder;

/**
 * Class DeleteSelect
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryPrinter
 */
class DeleteSelect implements IQueryPrinter
{
    /**
     * @var DeleteSelectBuilder $query
     */
    private $query;

    /**
     * DeleteSelect constructor.
     *
     * @param DeleteSelectBuilder $query
     */
    public function __construct(DeleteSelectBuilder $query)
    {
        $this->query = $query;
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

        $selectQueryPrinter = new Select($this->query->getData());

        return $delete . $selectQueryPrinter->printQuery();
    }
}
