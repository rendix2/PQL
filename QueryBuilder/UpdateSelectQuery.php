<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:59
 */

namespace pql\QueryBuilder;

use pql\Database;
use pql\QueryExecutor\UpdateSelectExecutor as UpdateSelectExecutor;
use pql\QueryResult\IResult;
use pql\QueryResult\TableResult;
use pql\Table;

class UpdateSelectQuery implements IQueryBuilder
{
    use WhereQueryBuilder;

    private Database $database;

    private IResult $result;

    private Query $data;

    /**
     * @var Table $table
     */
    private Table $table;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getData(): Query
    {
        return $this->data;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function updateSelect(Query $select, string $table): UpdateSelectQuery
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

        $updateSelect = new UpdateSelectExecutor($this);
        $affectedRows = $updateSelect->run();
        $endTime      = microtime(true);
        $executeTime  = $endTime - $startTime;

        return $this->result = new TableResult([], [], $executeTime, $updateSelect, $affectedRows);
    }
}
