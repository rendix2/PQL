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

/**
 * Class UpdateQuery
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder
 */
class UpdateQuery implements IQueryBuilder
{
    use WhereQueryBuilder;
    use LimitQueryBuilder;
    use Offset;

    /**
     * @var Database $database
     */
    private $database;

    /**
     * @var IResult $result
     */
    private $result;

    /**
     * @var Table $table
     */
    private $table;

    /**
     * @var array $data
     */
    private $data;

    /**
     * Update constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Update destructor.
     */
    public function __destruct()
    {
        $this->database = null;
        $this->result = null;
        $this->table = null;
        $this->data = null;
    }

    /**
     * @return array
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
     * @param string $table
     * @param array  $data
     *
     * @return UpdateQuery
     *
     */
    public function update($table, array $data)
    {
        $this->data  = $data;
        $this->table = new Table($this->database, $table);

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

        $update       = new UpdateExecutor($this);
        $affectedRows = $update->run();
        $endTime      = microtime(true);
        $executeTime  = $endTime - $startTime;

        return $this->result = new TableResult([], [], $executeTime, $update, $affectedRows);
    }
}
