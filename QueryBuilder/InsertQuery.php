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

/**
 * Class InsertQuery
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder
 */
class InsertQuery implements IQueryBuilder
{
    /**
     * @var Database $database
     */
    private $database;

    /**
     * @var Table $table
     */
    private $table;

    /**
     * @var array $data
     */
    private $data;

    /**
     * @var IResult $result
     */
    private $result;

    /**
     * Insert constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Insert destructor.
     */
    public function __destruct()
    {
        $this->database = null;
        $this->table = null;
        $this->data = null;
        $this->result = null;
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
        return $this->data;
    }

    /**
     * @param string $table
     * @param array  $data
     *
     * @return InsertQuery
     * @throws Exception
     */
    public function insert($table, array $data)
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

        $insert       = new InsertExecutor($this);
        $affectedRows = $insert->run();
        $endTime      = microtime(true);
        $executeTime  = $endTime - $startTime;

        return $this->result = new TableResult([], [], $executeTime, $insert, $affectedRows);
    }
}
