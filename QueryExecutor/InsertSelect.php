<?php

namespace pql\QueryExecutor;

use Exception;
use pql\Alias;
use pql\QueryBuilder\InsertSelect as InsertSelectBuilder;
use pql\QueryBuilder\Query;

/**
 * Class InsertSelect
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class InsertSelect implements IQueryExecutor
{
    /**
     * @var InsertSelectBuilder $query
     */
    private $query;

    /**
     * InsertSelect constructor.
     *
     * @param InsertSelectBuilder $query
     */
    public function __construct(InsertSelectBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function run()
    {
        $selectQuery = new Select($this->query->getData());
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

        $queryResult = $selectQuery->getResult();
        $result      = [];

        foreach ($queryResult as $rowNumber => $row) {
            foreach ($row as $columnName => $columnValue) {
                if (in_array($columnName, $selectedColumns, true)) {
                    $result[$rowNumber][$columnName] = $columnValue;
                }
            }
        }

        $results = [];

        foreach ($result as $insertData) {
            $insertQuery = new Query($this->query->getDatabase());
            $insertQuery->insert()->insert($this->query->getTable()->getName(), $insertData);

            $results[] = $insertQuery->run();
        }

        return $results;
    }
}
