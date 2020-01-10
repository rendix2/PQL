<?php

namespace pql\QueryExecute;

use pql\BTree\BtreeJ;
use pql\Table;
use pql\TableColumn;
use SplFileObject;

/**
 * Class Insert
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package QueryExecute
 */
class Insert extends BaseQuery
{

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
            foreach ($this->query->getInsertData() as $key => $data) {

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
