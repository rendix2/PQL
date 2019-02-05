<?php
namespace query;

use Query;

class Insert
{
    /**
     * @var Query $query
     */
    private $query;

    /**
     * Update constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Update destructor.
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
            foreach ($this->query->getInsertData() as $key => $data) {
                if ($column === $key) {
                    $row[] = $data;
                }
            }
        }

        return file_put_contents(
            $this->query->getTable()->getFileName(),
            implode(\Table::COLUMN_DELIMITER, $row),
            FILE_APPEND
        );
    }
}

