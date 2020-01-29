<?php

namespace pql\QueryExecute;

use Exception;
use pql\Alias;
use pql\Query;

/**
 * Class InsertSelect
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class InsertSelect extends BaseQuery
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function run()
    {
        $selectQuery = new Select($this->query->getInsertData());
        $selectQuery->run();

        $selectedColumns = [];

        foreach ($selectQuery->query->getSelectedColumns() as $selectedColumn) {
            if ($this->query->getTable()->columnExists($selectedColumn)) {
                $selectedColumns[] = $selectedColumn;
            }

            $exploded   = explode(Alias::DELIMITER, $selectedColumn);
            $lastColumn = count($exploded) - 1;

            if ($this->query->getTable()->columnExists($exploded[$lastColumn])) {
                $selectedColumns[$selectedColumn->getColumn()] = $exploded[$lastColumn];
            }
        }

        $queryResult = $selectQuery->result;
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
            $insertQuery->insert($this->query->getTable()->getName(), $insertData);

            $results[] = $insertQuery->run();
        }

        return $results;
    }
}
