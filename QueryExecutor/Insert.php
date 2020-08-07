<?php

namespace pql\QueryExecutor;

use pql\BTree\BtreeJ;
use pql\QueryBuilder\InsertQuery as InsertBuilder;
use pql\Table;
use pql\TableColumn;
use SplFileObject;

/**
 * Class Insert
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class Insert implements IQueryExecutor
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

        foreach ($this->query->getTable()->getColumns() as $column) {
            foreach ($this->query->getData() as $key => $data) {

                /**
                 * index maintenance
                 * @var TableColumn $column
                 */
                foreach ($this->query->getTable()->getIndexes() as $indexColumn => $indexFile) {
                    if ($column->getName() === $indexColumn) {
                        $columnRootIndex = BtreeJ::read($indexFile);

                        if ($columnRootIndex === false) {
                            $columnRootIndex = new BtreeJ();
                            $columnRootIndex->create($columnRootIndex);
                        }

                        $columnRootIndex->insert($data);
                        $columnRootIndex->write($this->query->getTable()->getIndexDir() . $indexFile);
                    }
                }

                if ($column->getName() === $key) {
                    $row[$column->getName()] = $data;
                }
            }

            if (!isset($row[$column->getName()])) {
                $row[$column->getName()] = 'null';
            }
        }

        $file = new SplFileObject($this->query->getTable()->getFilePath(), 'a');

        $line = implode(Table::COLUMN_DELIMITER, $row);

        $written = $file->fwrite(
            $line . $this->query->getTable()->getFileEnds(),
            strlen($line) + $this->query->getTable()->getFileEndsLength() // mb_strlen is bad! count as a one, not two!
        );
        $file = null;

        return $written;
    }
}
