<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 1. 2020
 * Time: 16:21
 */

namespace pql\QueryPrinter;

use pql\Query;

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
     * @var Query $query
     */
    private $query;

    /**
     * Insert constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
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
        $columns   = array_keys($this->query->getInsertData());
        $values    = array_values($this->query->getInsertData());
        $tableName = $this->query->getTable()->getName();

        $columns = '(' . implode(', ', $columns). ')';
        $values  = '(' . implode(', ', $values). ')';

        return 'INSERT INTO ' . $tableName . '  ' . $columns . ' VALUES ' . $values . '<br><br>';
    }
}
