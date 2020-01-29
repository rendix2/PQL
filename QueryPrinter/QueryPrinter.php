<?php

namespace pql\QueryPrinter;

use Exception;
use pql\Query;

/**
 * Class QueryPrinter
 *
 * @author rendix2 <rendix2@seznam.cz>
 * @package pql\QueryPrinter
 */
class QueryPrinter
{
    /**
     * @var IQueryPrinter $query
     */
    private $query;

    /**
     * QueryPrinter constructor.
     *
     * @param Query $query
     *
     * @throws Exception
     */
    public function __construct(Query $query)
    {
        // decide which Query type we have
        switch ($query->getType()) {
            case Query::SELECT:
                $this->query = new Select($query);
                break;
            case Query::INSERT:
                $this->query = new Insert($query);
                break;
            case Query::DELETE:
                $this->query = new Delete($query);
                break;
            case Query::UPDATE:
                $this->query = new Update($query);
                break;
            case Query::EXPLAIN:
                $this->query = new Explain($query);
                break;
            case Query::INSERT_SELECT:
                $this->query = new InsertSelect($query);
                break;
            case Query::DELETE_SELECT:
                $this->query = new DeleteSelect($query);
                break;
            default:
                $message = sprintf('Unknown query type "%s".', $query->getType());

                throw new Exception($message);
        }
    }

    /**
     * QueryPrinter destructor.
     */
    public function __destruct()
    {
        $this->query = null;
    }

    /**
     * @return string
     */
    public function printQuery()
    {
        return $this->query->printQuery();
    }
}
