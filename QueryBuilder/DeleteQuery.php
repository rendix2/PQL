<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:58
 */

namespace pql\QueryBuilder;

use pql\Database;
use pql\QueryExecutor\DeleteExecutor as DeleteExecutor;
use pql\QueryResult\IResult;
use pql\QueryResult\TableResult;
use pql\Table;

/**
 * Class DeleteQuery
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder
 */
class DeleteQuery implements IQueryBuilder
{
    use From;
    use WhereQueryBuilder;
    use LimitQueryBuilder;
    use Offset;

    /**
     * @var Database $database
     */
    private Database $database;

    /**
     * @var array $data
     */
    private array $data;

    /**
     * @var IResult $result
     */
    private IResult $result;

    /**
     * Delete constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->table = null;
    }


    public function getResult(): IResult
    {
        return $this->result;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getData(): array
    {
        return  $this->data;
    }

    /**
     * @param string $table
     *
     * @return DeleteQuery
     * @throws \Exception
     */
    public function delete(string $table): DeleteQuery
    {
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

        $delete       = new DeleteExecutor($this);
        $affectedRows = $delete->run();
        $endTime      = microtime(true);
        $executeTime  = $endTime - $startTime;

        return $this->result = new TableResult([], [], $executeTime, $delete, $affectedRows);
    }
}
