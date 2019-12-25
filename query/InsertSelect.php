<?php

namespace query;

use Alias;
use Exception;
use Query;

/**
 * Class InsertSelect
 *
 * @package query
 */
class InsertSelect extends BaseQuery
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function run()
    {
        $select = new Select($this->query->getInsertData());
        $select->run();

        $selectedColumns = [];

        foreach ($select->query->getSelectedColumns() as $selectedColumn) {
            if ($this->query->getTable()->columnExists($selectedColumn)) {
                $selectedColumns[] = $selectedColumn;
            }

            $exploded = explode(Alias::DELIMITER, $selectedColumn);
            $explodedCount = count($exploded);

            if ($this->query->getTable()->columnExists($exploded[$explodedCount-1])) {
                $selectedColumns[$selectedColumn] = $exploded[$explodedCount-1];
            }
        }

        $queryResult = $select->result;
        $result = [];

        foreach ($queryResult as $rowNumber => $row) {
            foreach ($row as $columnName => $columnValue) {
                if (in_array($columnName, $selectedColumns, true)) {
                    $result[$rowNumber][$columnName] = $columnValue;
                }
            }
        }

        foreach ($result as $insertData) {
            $insertQuery = new Query($this->query->getDatabase());
            $insertQuery->insert($this->query->getTable()->getName(), $insertData);
            $insertQuery->run();
        }
    }
}
