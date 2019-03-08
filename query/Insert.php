<?php
namespace query;

use Table;
use BTree\BtreeJ;

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
                 * index maitaince
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

                /*
                if ($column->getType() !== Help::getType($data[$column])) {
                    throw new Exception('Incorrect data type.');
                }
                */

                if ($column->getName() === $key) {
                    $row[] = $data;
                }
            }
        }

        return file_put_contents(
            $this->query->getTable()->getFilePath(),
            "\n" . implode(Table::COLUMN_DELIMITER, $row),
            FILE_APPEND
        );
    }
}

