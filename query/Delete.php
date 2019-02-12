<?php
namespace query;

use Nette\Utils\FileSystem;
use Query;
use SplFileObject;

class Delete
{
    /**
     * @var Query $query
     */
    private $query;

    private $res;

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
        $this->res   = null;
    }

    public function run()
    {
        $this->where();
        $this->limit();       
        
        $tmpFileName = $this->query->getTable()->getFileName() . '.tmp';
        
        $fileTemp = new SplFileObject($tmpFileName, 'a');
        
        $fileTemp->fwrite($this->query->getTable()->getColumnsString());
        
        foreach ($this->query->getTable()->getRows() as $line => $row) {
            if (!in_array($line, $this->res, true)) {
                $fileTemp->fwrite(implode(\Table::COLUMN_DELIMITER, $row). "\n");
            }
        }
        
        $fileTemp = null;        
        $tmpFile  = file_get_contents($tmpFileName);
        
        file_put_contents($this->query->getTable()->getFileName(), $tmpFile);
                
        FileSystem::delete($tmpFileName);

        return count($this->res);
    }

    private function where()
    {
        $wheres = $this->query->getWhereCondition();
        $rows   = $this->query->getTable()->getRows();
        $res    = [];

        foreach ($wheres as $where) {
            foreach ($rows as $rowNumber => $row) {
                foreach ($row as $column => $value) {
                    if ($where['column'] === $column) {
                        if ($where['operator'] === '=') {
                            if ($where['value'] === $value) {
                              $res[] = $rowNumber;
                            }
                        }

                        if ($where['operator'] === '>') {
                            if ($where['value'] > $value) {
                                $res[] = $rowNumber;
                            }
                        }

                        if ($where['operator'] === '>=') {
                            if ($where['value'] >= $value) {
                                $res[] = $rowNumber;
                            }
                        }

                        if ($where['operator'] === '<') {
                            if ($where['value'] < $value) {
                                $res[] = $rowNumber;
                            }
                        }

                        if ($where['operator'] === '<=') {
                            if ($where['value'] <= $value) {
                                $res[] = $rowNumber;
                            }
                        }

                        if ($where['operator'] === '!=' || $where['operator'] === '<>') {
                            if ($where['value'] !== $value) {
                                $res[] = $rowNumber;
                            }
                        }
                    }
                }
            }
        }

        $this->res = $res;
    }

    private function limit()
    {
        $limit = $this->query->getLimit();

        $this->res = array_slice($this->res, $limit);
    }
}

