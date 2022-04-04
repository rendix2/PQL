<?php
/**
 *
 * Created by PhpStorm.
 * Filename: InsertBuilder.php
 * User: Tomáš Babický
 * Date: 17.09.2021
 * Time: 23:10
 */

namespace PQL\Database\Query\Printer;

use PQL\Database\Query\Builder\InsertBuilder;

/**
 * Class InsertPrinter
 *
 * @package PQL\Database\Query\Printer
 */
class InsertPrinter
{
    /**
     * @var InsertBuilder $insert
     */
    private InsertBuilder $insert;

    /**
     * @param InsertBuilder $insert
     */
    public function __construct(InsertBuilder $insert)
    {
        $this->insert = $insert;
    }

    public function print() : string
    {
        $columns = array_keys($this->insert->getData());
        $values = array_values($this->insert->getData());

        return 'INSERT INTO ' . $this->insert->getTable()->evaluate() . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ')';
    }
}
