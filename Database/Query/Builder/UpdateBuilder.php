<?php
/**
 *
 * Created by PhpStorm.
 * Filename: UpdateBuilder.php
 * User: Tomáš Babický
 * Date: 21.09.2021
 * Time: 21:41
 */

namespace PQL\Database\Query\Builder;

use PQL\Database\Database;
use PQL\Database\Query\Builder\Expressions\Set;
use PQL\Database\Query\Builder\Expressions\TableExpression;
use PQL\Database\Query\Builder\Expressions\WhereCondition;
use PQL\Database\Query\Printer\UpdatePrinter;
use PQL\Query\UpdateExecutor;

class UpdateBuilder
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
     * @var Set[] $setExpressions
     */
    private array $setExpressions;

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

    public function table(TableExpression $tableExpression) : static
    {
        $this->table = $tableExpression;

        return $this;
    }

    public function set(Set $set) : static
    {
        $this->setExpressions[] = $set;

        return $this;
    }

    public function where(WhereCondition $whereCondition) : static
    {
        $this->whereConditions[] = $whereCondition;

        return $this;
    }

    public function print() : string
    {
        $updatePrinter = new UpdatePrinter($this);

        return $updatePrinter->print();
    }

    public function execute() : bool
    {
        $updateExecutor = new UpdateExecutor($this);

        return $updateExecutor->run();
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
     * @return Set[]
     */
    public function getSetExpressions() : array
    {
        return $this->setExpressions;
    }

    /**
     * @return WhereCondition[]
     */
    public function getWhereConditions() : array
    {
        return $this->whereConditions;
    }
}
