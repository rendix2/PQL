<?php

namespace pql\QueryExecutor;

use pql\QueryBuilder\InsertQuery as InsertBuilder;
use pql\Table;
use SplFileObject;

/**
 * Class Insert
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class InsertExecutor implements IQueryExecutor
{
    /**
     * @var InsertBuilder $query
     */
    private $query;

    /**
     * Insert constructor.
     *
     * @param InsertBuilder $query
     */
    public function __construct(InsertBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * Insert destructor.
     */
    public function __destruct()
    {
        $this->query = null;
    }

    /**
     *
     */
    public function run()
    {
        return $this->insert();
    }

    /**
     *
     */
    private function insert()
    {
        $row = [];
        $indexData = [];

        foreach ($this->query->getTable()->getColumns() as $column) {
            foreach ($this->query->getData() as $key => $data) {
                if ($column->getName() === $key) {
                    $row[$column->getName()] = $data;
                }
            }

            if (!isset($row[$column->getName()])) {
                $row[$column->getName()] = 'null';
            }
        }

        foreach ($this->query->getData() as $column => $value) {
            foreach ($this->query->getTable()->getIndexNames() as $indexName => $indexFile) {
                if ($column === $indexName) {
                    $indexData[$column][] = $value;

                    break;
                }
            }
        }

        foreach ($this->query->getTable()->getIndexes() as $tableColumnIndexName => $indexInstance) {
            foreach ($indexData as $indexDataColumnName => $indexDataValues) {
                if ($tableColumnIndexName === $indexDataColumnName) {
                    foreach ($indexDataValues as $indexDataValue ) {
                        $indexInstance->insert(['value' => $indexDataValue,  'rowNumber' => $this->query->getTable()->getRowsCount()+1]);
                        $indexInstance->write();
                    }
                }
            }
        }

        $file = new SplFileObject($this->query->getTable()->getFilePath(), 'a');

        $line = implode(Table::COLUMN_DELIMITER, $row);

        $written = $file->fwrite(
            $line . $this->query->getTable()->getFileEnds(),
            strlen($line) + $this->query->getTable()->getFileEndsLength() // mb_strlen is bad! count space as a one, not two!
        );
        $file = null;

        return $written;
    }
}
