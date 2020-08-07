<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:59
 */

namespace pql\QueryBuilder;

use pql\Database;
use pql\QueryExecutor\DeleteSelect as DeleteSelectExecutor;
use pql\QueryResult\IResult;
use pql\QueryResult\TableResult;
use pql\Table;

/**
 * Class DeleteSelectQuery
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder
 */
class DeleteSelectQuery implements IQueryBuilder
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
     * @var Query $data
     */
    private $data;

    /**
     * @var IResult $result
     */
    private $result;

    /**
     * DeleteSelect constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * DeleteSelect destructor.
     */
    public function __destruct()
    {
        $this->database = null;
        $this->table = null;
        $this->data = null;
        $this->result = null;
    }

    /**
     * @return Query
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param Query  $select
     * @param string $table
     *
     * @return DeleteSelectQuery
     */
    public function deleteSelect(Query $select, $table)
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

        $deleteSelect = new DeleteSelectExecutor($this);
        $affectedRows = $deleteSelect->run();
        $endTime      = microtime(true);
        $executeTime  = $endTime - $startTime;

        return $this->result = new TableResult([], [], $executeTime, $deleteSelect, $affectedRows);
    }
}
