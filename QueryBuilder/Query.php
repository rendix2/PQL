<?php

namespace pql\QueryBuilder;

use Exception;
use pql\Database;
use pql\QueryPrinter\InsertSelect;
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
    private IQueryBuilder $query;

    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function __toString(): string
    {
        $queryPrinter = new QueryPrinter($this, 0);

        return $queryPrinter->printQuery();
    }

    public function delete(): DeleteQuery
    {
        return $this->query = new DeleteQuery($this->database);
    }

    public function deleteSelect(): DeleteSelectQuery
    {
        return $this->query = new DeleteSelectQuery($this->database);
    }

    public function explain(): ExplainQuery
    {
        return $this->query = new ExplainQuery($this->database);
    }

    /**
     * @return InsertQuery
     */
    public function insert(): InsertQuery
    {
        return $this->query = new InsertQuery($this->database);
    }

    public function insertSelect(): InsertSelectQuery
    {
        return $this->query = new InsertSelectQuery($this->database);
    }

    public function select(): SelectQuery
    {
        return $this->query = new SelectQuery($this->database);
    }

    public function update(): UpdateQuery
    {
        return $this->query = new UpdateQuery($this->database);
    }

    /**
     * @return UpdateSelectQuery
     */
    public function updateSelect(): UpdateSelectQuery
    {
        return $this->query = new UpdateSelectQuery($this->database);
    }

    public function getQuery(): IQueryBuilder
    {
        return $this->query;
    }

    public function run(): mixed
    {
        if ($this->query === null) {
            $message = 'Query is not set.';

            throw new Exception($message);
        }

        return $this->query->run();
    }
}
