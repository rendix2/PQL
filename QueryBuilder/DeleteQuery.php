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
    private $database;

    /**
     * @var array $data
     */
    private $data;

    /**
     * @var IResult $result
     */
    private $result;

    /**
     * Delete constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Delete destructor.
     */
    public function __destruct()
    {
        $this->database = null;
        $this->data = null;
        $this->result = null;
    }

    /**
     * @return IResult
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return  $this->data;
    }

    /**
     * @param string $table
     *
     * @return DeleteQuery
     * @throws \Exception
     */
    public function delete($table)
    {
        if (is_string($table)) {
            $this->table = new Table($this->database, $table);
        } else {
            $message = 'Delete query supports only table name for input.';

            throw new \Exception($message);
        }

        return $this;
    }

    /**
     * @return IResult|TableResult
     */
    public function run()
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
