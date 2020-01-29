<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 29. 1. 2020
 * Time: 16:34
 */

namespace pql\QueryExecute;

use pql\Alias;
use pql\Operator;
use pql\Query;

/**
 * Class DeleteSelect
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class DeleteSelect extends BaseQuery
{
    /**
     * @inheritDoc
     */
    public function run()
    {
        $selectQuery = new Select($this->query->getDeleteData());
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

        $results = [];

        foreach ($selectQuery->result as $row) {
            $deleteQuery = new Query($this->query->getDatabase());
            $deleteQuery->delete($this->query->getTable()->getName());

            foreach ($selectedColumns as $selectedColumn) {
                $deleteQuery = $deleteQuery->where($selectedColumn, Operator::EQUAL, $row[$selectedColumn]);
            }

            $results[] = $deleteQuery->run();
        }

        return $results;
    }
}
