<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:59
 */

namespace pql\QueryBuilder;

use pql\Database;
use pql\QueryExecutor\UpdateExecutor as UpdateExecutor;
use pql\QueryResult\IResult;
use pql\QueryResult\TableResult;
use pql\Table;

class UpdateQuery implements IQueryBuilder
{
    use WhereQueryBuilder;
    use LimitQueryBuilder;
    use Offset;

    private Database $database;

    private IResult $result;

    private Table $table;

    private array $data;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function update(string $table, array $data): UpdateQuery
    {
        $this->data  = $data;
        $this->table = new Table($this->database, $table);

        return $this;
    }

    public function run(): IResult|TableResult
    {
        if ($this->result instanceof TableResult) {
            return $this->result;
        }

        set_time_limit(0);

        $startTime = microtime(true);

        $update       = new UpdateExecutor($this);
        $affectedRows = $update->run();
        $endTime      = microtime(true);
        $executeTime  = $endTime - $startTime;

        return $this->result = new TableResult([], [], $executeTime, $update, $affectedRows);
    }
}
