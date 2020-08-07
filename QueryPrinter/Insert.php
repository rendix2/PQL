<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 1. 2020
 * Time: 16:21
 */

namespace pql\QueryPrinter;

use pql\QueryBuilder\InsertQuery as InsertBuilder;

/**
 * Class Insert
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryPrinter
 * @internal
 */
class Insert implements IQueryPrinter
{
    /**
     * @var InsertBuilder $query
     */
    private $query;

    /**
     * Insert constructor.
     *
     * @param InsertBuilder $query
     */
    public function __construct(InsertBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * Insert destructor.
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
        $columns   = array_keys($this->query->getData());
        $values    = array_values($this->query->getData());
        $tableName = $this->query->getTable()->getName();

        $columns = '(' . implode(', ', $columns). ')';
        $values  = '(' . implode(', ', $values). ')';

        return 'INSERT INTO ' . $tableName . '  ' . $columns . ' VALUES ' . $values . '<br><br>';
    }
}
