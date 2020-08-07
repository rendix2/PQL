<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:59
 */

namespace pql\QueryBuilder;

use pql\Database;
use pql\QueryExecutor\InsertSelect as InsertSelectExecutor;
use pql\QueryResult\IResult;
use pql\QueryResult\TableResult;
use pql\Table;

/**
 * Class InsertSelectQuery
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder
 */
class InsertSelectQuery implements IQueryBuilder
{
    /**
     * @var Database $database
     */
    private $database;

    /**
     * @var Query $data
     */
    private $data;

    /**
     * @var IResult $result
     */
    private $result;

    /**
     * @var Table $table
     */
    private $table;

    /**
     * InsertSelect constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * InsertSelect destructor.
     */
    public function __destruct()
    {
        $this->database = null;
        $this->data = null;
        $this->result = null;
        $this->table = null;
    }

    /**
     * @return Query
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param Query  $select
     * @param string $table
     *
     * @return InsertSelectQuery
     */
    public function insertSelect(Query $select, $table)
    {
        $this->table = new Table($this->database, $table);

        $this->data = $select;

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

        $insertSelect = new InsertSelectExecutor($this);
        $affectedRows = $insertSelect->run();
        $endTime      = microtime(true);
        $executeTime  = $endTime - $startTime;

        return $this->result = new TableResult([], [], $executeTime, $insertSelect, $affectedRows);
    }
}
