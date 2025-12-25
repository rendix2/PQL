<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 29. 1. 2020
 * Time: 16:34
 */

namespace pql\QueryExecutor;

use pql\Alias;
use pql\Operator;
use pql\QueryBuilder\DeleteSelectQuery as DeleteSelectBuilder;
use pql\QueryBuilder\Operator\Equals;
use pql\QueryBuilder\Query;

/**
 * Class DeleteSelect
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class DeleteSelectExecutor implements IQueryExecutor
{
    private DeleteSelectBuilder $query;

    public function __construct(DeleteSelectBuilder $query)
    {
        $this->query = $query;
    }
    public function run()
    {
        $selectQuery = new SelectExecutor($this->query->getData());
        $selectQuery->run();

        $selectedColumns = [];

        foreach ($selectQuery->getQuery()->getSelectedColumns() as $selectedColumn) {
            if ($this->query->getTable()->columnExists($selectedColumn)) {
                $selectedColumns[] = $selectedColumn;
            }

            $exploded   = explode(Alias::DELIMITER, $selectedColumn);
            $lastColumn = count($exploded) - 1;

            if ($this->query->getTable()->columnExists($exploded[$lastColumn])) {
                $selectedColumns[$selectedColumn->getColumn()] = $exploded[$lastColumn];
            }
        }

        $results = [];

        foreach ($selectQuery->getResult() as $row) {
            $deleteQuery = new Query($this->query->getDatabase());
            $deleteQuery = $deleteQuery->delete()->delete($this->query->getTable()->getName());

            foreach ($selectedColumns as $selectedColumn) {
                $deleteQuery = $deleteQuery->where($selectedColumn, new Equals(), $row[$selectedColumn]);
            }

            $results[] = $deleteQuery->run();
        }

        return $results;
    }
}
