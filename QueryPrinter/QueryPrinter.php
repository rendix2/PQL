<?php

namespace pql\QueryPrinter;

use Exception;
use pql\QueryBuilder\Delete as DeleteBuilder;
use pql\QueryBuilder\DeleteSelect as DeleteSelectBuilder;
use pql\QueryBuilder\Explain as ExplainBuilder;
use pql\QueryBuilder\Insert as InsertBuilder;
use pql\QueryBuilder\InsertSelect as InsertSelectBuilder;
use pql\QueryBuilder\Query;
use pql\QueryBuilder\Select as SelectBuilder;
use pql\QueryBuilder\Update as UpdateBuilder;
use pql\QueryBuilder\UpdateSelect as UpdateSelectBuilder;

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

        $className = get_class($query->getQuery());

        switch ($className) {
            case SelectBuilder::class:
                $this->query = new Select($query->getQuery());
                break;
            case InsertBuilder::class:
                $this->query = new Insert($query->getQuery());
                break;
            case DeleteBuilder::class:
                $this->query = new Delete($query->getQuery());
                break;
            case UpdateBuilder::class:
                $this->query = new Update($query->getQuery());
                break;
            case ExplainBuilder::class:
                $this->query = new Explain($query->getQuery());
                break;
            case InsertSelectBuilder::class:
                $this->query = new InsertSelect($query->getQuery());
                break;
            case DeleteSelectBuilder::class:
                $this->query = new DeleteSelect($query->getQuery());
                break;
            case UpdateSelectBuilder::class:
                $this->query = new UpdateSelect($query->getQuery());
                break;
            default:
                $message = sprintf('Unknown query type "%s".', $className);

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
