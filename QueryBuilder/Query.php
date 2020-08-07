<?php

namespace pql\QueryBuilder;

use Exception;
use pql\Database;
use pql\QueryPrinter\QueryPrinter;

/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:46
 */

/**
 * Class Query
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder
 */
class Query
{
    /**
     * @var IQueryBuilder $query
     */
    private $query;

    /**
     * @var Database $database
     */
    private $database;

    /**
     * Query constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Query destructor.
     */
    public function __destruct()
    {
        $this->query    = null;
        $this->database = null;
    }

    /**
     * prints query in SQL
     *
     * @return string
     */
    public function __toString()
    {
        $queryPrinter = new QueryPrinter($this, 0);

        return $queryPrinter->printQuery();
    }

    /**
     * @return DeleteQuery
     */
    public function delete()
    {
        return $this->query = new DeleteQuery($this->database);
    }

    /**
     * @return DeleteSelectQuery
     */
    public function deleteSelect()
    {
        return $this->query = new DeleteSelectQuery($this->database);
    }

    /**
     * @return ExplainQuery
     */
    public function explain()
    {
        return $this->query = new ExplainQuery($this->database);
    }

    /**
     * @return InsertQuery
     */
    public function insert()
    {
        return $this->query = new InsertQuery($this->database);
    }

    public function insertSelect()
    {
        return $this->query = new InsertSelectQuery($this->database);
    }

    /**
     * @return SelectQuery
     */
    public function select()
    {
        return $this->query = new SelectQuery($this->database);
    }

    /**
     * @return UpdateQuery
     */
    public function update()
    {
        return $this->query = new UpdateQuery($this->database);
    }

    /**
     * @return UpdateSelectQuery
     */
    public function updateSelect()
    {
        return $this->query = new UpdateSelectQuery($this->database);
    }

    /**
     * @return IQueryBuilder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function run()
    {
        if ($this->query === null) {
            $message = 'Query is not set.';

            throw new Exception($message);
        }

        return $this->query->run();
    }
}
