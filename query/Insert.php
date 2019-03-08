<?php
namespace query;

use Query;
use Table;
use BTree\BtreeJ;

class Insert
{
    /**
     * @var Query $query
     */
    private $query;
    
    private $changes;

    /**
     * Update constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query   = $query;
        
        $this->changes = [
            'indexes' => [],
            'table'   => null,
            'newRows' => 0
        ];
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
    
    public function getChanges()
    {
        return $this->changes;
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
                        if (!in_array($indexColumn, $this->changes['indexes'])) {
                            $this->changes['indexes'][] = $indexColumn;
                        }
                        
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
        
        $this->changes['table'] = $this->query->getTable()->getName();
        
        return file_put_contents(
            $this->query->getTable()->getFilePath(),
            "\n" . implode(Table::COLUMN_DELIMITER, $row),
            FILE_APPEND
        );
    }
}

