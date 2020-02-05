<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:59
 */

namespace pql\QueryBuilder;

use pql\Database;
use pql\QueryExecutor\UpdateSelect as UpdateSelectExecutor;
use pql\QueryResult\IResult;
use pql\QueryResult\TableResult;
use pql\Table;

/**
 * Class UpdateSelect
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder
 */
class UpdateSelect implements IQueryBuilder
{
    use Where;

    /**
     * @var Database $database
     */
    private $database;

    /**
     * @var IResult $result
     */
    private $result;

    /**
     * @var Query $data
     */
    private $data;

    /**
     * @var Table $table
     */
    private $table;

    /**
     * UpdateSelect constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * UpdateSelect destructor.
     */
    public function __destruct()
    {
        $this->database = null;
        $this->result = null;
        $this->data = null;
        $this->table = null;
    }

    /**
     * @return mixed
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
     * @return UpdateSelect
     */
    public function updateSelect(Query $select, $table)
    {
        $this->table = new Table($this->database, $table);

        $this->data = $select;

        return $this;
    }

    public function run()
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
