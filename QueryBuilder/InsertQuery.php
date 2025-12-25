<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:58
 */

namespace pql\QueryBuilder;

use Exception;
use pql\Database;
use pql\QueryExecutor\InsertExecutor as InsertExecutor;
use pql\QueryResult\IResult;
use pql\QueryResult\TableResult;
use pql\Table;

class InsertQuery implements IQueryBuilder
{
    private Database $database;

    private Table $table;

    private array $data;

    private ?IResult $result;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->result = null;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function insert(string $table, array $data): InsertQuery
    {
        $this->table = new Table($this->database, $table);

        $this->data = $data;

        $columns = array_keys($data);

        foreach ($columns as $column) {
            if (!$this->table->columnExists($column)) {
                $message = sprintf('Column "%s" does not exist in "%s".', $column, $table);

                throw new Exception($message);
            }
        }

        return $this;
    }

    public function run():IResult|TableResult
    {
        if ($this->result instanceof TableResult) {
            return $this->result;
        }

        set_time_limit(0);

        $startTime = microtime(true);

        $insert       = new InsertExecutor($this);
        $affectedRows = $insert->run();
        $endTime      = microtime(true);
        $executeTime  = $endTime - $startTime;

        return $this->result = new TableResult([], [], $executeTime, $insert, $affectedRows);
    }
}
