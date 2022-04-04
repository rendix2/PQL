<?php
/**
 *
 * Created by PhpStorm.
 * Filename: DeleteBuilder.php
 * User: Tomáš Babický
 * Date: 21.09.2021
 * Time: 21:42
 */

namespace PQL\Database\Query\Builder;

use PQL\Database\Database;
use PQL\Database\Query\Builder\Expressions\TableExpression;
use PQL\Database\Query\Builder\Expressions\WhereCondition;
use PQL\Database\Query\Printer\DeletePrinter;
use PQL\Query\DeleteExecutor;

/**
 * Class DeleteBuilder
 *
 * @package PQL\Database\Query\Builder
 */
class DeleteBuilder
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
     * @var WhereCondition[] $whereConditions
     */
    private array $whereConditions;

    /**
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;

        $this->whereConditions = [];
    }

    /**
     * @param TableExpression $tableExpression
     *
     * @return $this
     */
    public function from(TableExpression $tableExpression) : static
    {
        $this->table = $tableExpression;

        return $this;
    }

    /**
     * @param WhereCondition $whereCondition
     *
     * @return $this
     */
    public function where(WhereCondition $whereCondition) : static
    {
        $this->whereConditions[] = $whereCondition;

        return $this;
    }

    /**
     * @return string
     */
    public function print() : string
    {
        $deletePrinter = new DeletePrinter($this);

        return $deletePrinter->print();
    }

    /**
     * @return bool
     */
    public function execute() : bool
    {
        $deleteExecutor = new DeleteExecutor($this);

        return $deleteExecutor->run();
    }


    // GETTERS


    /**
     * @return Database
     */
    public function getDatabase() : Database
    {
        return $this->database;
    }

    /**
     * @return TableExpression
     */
    public function getTable() : TableExpression
    {
        return $this->table;
    }

    /**
     * @return WhereCondition[]
     */
    public function getWhereConditions() : array
    {
        return $this->whereConditions;
    }
}