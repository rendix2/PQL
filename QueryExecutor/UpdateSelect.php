<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 29. 1. 2020
 * Time: 17:00
 */

namespace pql\QueryExecutor;

use pql\QueryBuilder\Query;
use pql\QueryBuilder\UpdateSelect as UpdateSelectBuilder;
use pql\QueryBuilder\Where;

/**
 * Class UpdateSelect
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class UpdateSelect implements IQueryExecutor
{
    use Where;

    /**
     * @var UpdateSelectBuilder $query
     */
    private $query;

    /**
     * UpdateSelect constructor.
     *
     * @param UpdateSelectBuilder $query
     */
    public function __construct(UpdateSelectBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * UpdateSelect destructor.
     */
    public function __destruct()
    {
        $this->query = null;
    }

    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $selectQuery = new Select($this->query->getData());
        $selectQuery->run();

        $results = [];

        foreach ($selectQuery->getQuery() as $updateData) {
            $updateQuery = new Query($this->query->getDatabase());
            $updateQuery = $updateQuery->update()->update($this->query->getTable()->getName(), $updateData);

            foreach ($this->query->getWhereConditions() as $whereCondition) {
                $updateQuery = $updateQuery->where(
                    $whereCondition->getColumn(),
                    $whereCondition->getOperator(),
                    $whereCondition->getValue()
                );
            }

            if ($this->query->getLimit()) {
                $updateQuery->limit($this->query->getLimit());
            }

            if ($this->query->getOffset()) {
                $updateQuery->offset($this->query->getOffset());
            }

            $results[] = $updateQuery->run();
        }

        return $results;
    }
}
