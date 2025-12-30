<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:59
 */

namespace pql\QueryBuilder;

use pql\Database;
use pql\QueryExecutor\DeleteSelectExecutor as DeleteSelectExecutor;
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
    private Database $database;

    /**
     * @var Table $table
     */
    private Table $table;

    /**
     * @var Query $data
     */
    private Query $data;

    /**
     * @var IResult $result
     */
    private IResult $result;

    /**
     * DeleteSelect constructor.
     *
     * @param Database $database
     */
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

    /**
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    public function deleteSelect(Query $select, string $table): DeleteSelectQuery
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

        $deleteSelect = new DeleteSelectExecutor($this);
        $affectedRows = $deleteSelect->run();
        $endTime      = microtime(true);
        $executeTime  = $endTime - $startTime;

        return $this->result = new TableResult([], [], $executeTime, $deleteSelect, $affectedRows);
    }
}
