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
        $this->database =  $database;
    }

    /**
     * Query destructor.
     */
    public function __destruct()
    {
        $this->query = null;
        $this->database = null;
    }

    /**
     * prints query in SQL
     *
     * @return string
     */
    public function __toString()
    {
        $queryPrinter = new QueryPrinter($this);

        return $queryPrinter->printQuery();
    }

    public function delete()
    {
        return $this->query = new Delete($this->database);
    }

    public function deleteSelect()
    {
        return $this->query = new DeleteSelect($this->database);
    }

    public function explain()
    {
        return $this->query = new Explain($this->database);
    }

    public function insert()
    {
        return $this->query = new Insert($this->database);
    }

    public function select()
    {
        return $this->query = new Select($this->database);
    }

    public function update()
    {
        return $this->query = new Update($this->database);
    }

    public function updateSelect()
    {
        return $this->query = new UpdateSelect($this->database);
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
