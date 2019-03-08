<?php
namespace query;

use Table;

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
            $this->query->getTable()->getFileName(),
            "\n" . implode(Table::COLUMN_DELIMITER, $row),
            FILE_APPEND
        );
    }
}

