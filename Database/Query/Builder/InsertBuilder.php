<?php
/**
 *
 * Created by PhpStorm.
 * Filename: InsertBuilder.php
 * User: Tomáš Babický
 * Date: 17.09.2021
 * Time: 22:40
 */

namespace PQL\Database\Query\Builder;

use PQL\Database\Database;
use PQL\Database\Query\Builder\Expressions\TableExpression;
use PQL\Database\Query\Executor\InsertExecutor;
use PQL\Database\Query\Printer\InsertPrinter;

/**
 * Class InsertBuilder
 *
 * @package PQL\Database\Query\Builder
 */
class InsertBuilder implements IQuery
{
    /**
     * @var Database $database
     */
    private Database $database;

    /**
     * @var TableExpression $table
     */
    private TableExpression $table;

    /**
     * @var array $data
     */
    private array $data;

    /**
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @param TableExpression $tableExpression
     */
    public function into(TableExpression $tableExpression)
    {
        $this->table = $tableExpression;
    }

    /**
     * @param array $data
     */
    public function values(array $data) : void
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function execute() : bool
    {
        $insertExecutor = new InsertExecutor($this);

        return $insertExecutor->run();
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null) : string
    {
        $insertPrinter = new InsertPrinter($this);

        return $insertPrinter->print();
    }

    /**
     * @return TableExpression
     */
    public function getTable() : TableExpression
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }
}