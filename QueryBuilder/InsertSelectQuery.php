<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:59
 */

namespace pql\QueryBuilder;

use pql\Database;
use pql\QueryExecutor\InsertSelectExecutor as InsertSelectExecutor;
use pql\QueryResult\IResult;
use pql\QueryResult\TableResult;
use pql\Table;

class InsertSelectQuery implements IQueryBuilder
{

    private Database $database;

    private Query $data;

    private IResult $result;

    private Table $table;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getData(): Query
    {
        return $this->data;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function insertSelect(Query $select, string$table): InsertSelectQuery
    {
        $this->table = new Table($this->database, $table);

        $this->data = $select;

        return $this;
    }

    public function run(): IResult|TableResult
    {
        if ($this->result instanceof TableResult) {
            return $this->result;
        }

        set_time_limit(0);

        $startTime = microtime(true);

        $insertSelect = new InsertSelectExecutor($this);
        $affectedRows = $insertSelect->run();
        $endTime      = microtime(true);
        $executeTime  = $endTime - $startTime;

        return $this->result = new TableResult([], [], $executeTime, $insertSelect, $affectedRows);
    }
}
