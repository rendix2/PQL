<?php

namespace pql\QueryPrinter;

use Exception;

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
     * @param \pql\QueryBuilder\Query $query
     *
     * @throws Exception
     */
    public function __construct(\pql\QueryBuilder\Query $query)
    {
        // decide which Query type we have

        switch (get_class($query->getQuery())) {
            case \pql\QueryBuilder\Select::class:
                $this->query = new Select($query->getQuery());
                break;
            case \pql\QueryBuilder\Insert::class:
                $this->query = new Insert($query->getQuery());
                break;
            case \pql\QueryBuilder\Delete::class:
                $this->query = new Delete($query->getQuery());
                break;
            case \pql\QueryBuilder\Update::class:
                $this->query = new Update($query->getQuery());
                break;
            case \pql\QueryBuilder\Explain::class:
                $this->query = new Explain($query->getQuery());
                break;
            case \pql\QueryBuilder\InsertSelect::class:
                $this->query = new InsertSelect($query->getQuery());
                break;
            case \pql\QueryBuilder\DeleteSelect::class:
                $this->query = new DeleteSelect($query->getQuery());
                break;
            case \pql\QueryBuilder\UpdateSelect::class:
                $this->query = new UpdateSelect($query->getQuery());
                break;
            default:
                $message = sprintf('Unknown query type "%s".', get_class($query->getQuery()));

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
