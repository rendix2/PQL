<?php
namespace query;

use Query;
use SplFileObject;
use Table;
use Nette\Utils\FileSystem;

/**
 * Class Update
 *
 * @author  rendix2
 * @package query
 */
class Update
{
    /**
     * @var Query $query
     */
    private $query;

    /**
     * @var array $res
     */
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

    /**
     *
     */
    public function run()
    {
        $this->where();
        $this->limit();
        
        $tmpFileName = $this->query->getTable()->getFileName() . '.tmp';

        $file = new SplFileObject($tmpFileName,'a');
        
        $file->fwrite($this->query->getTable()->getColumnsString());
        
        foreach ($this->res as $values) {
            $file->fwrite(implode(Table::COLUMN_DELIMITER, $values) . "\n");
        }
        
        $file = null;
        
        $tmp = file_get_contents($tmpFileName);
        file_put_contents($this->query->getTable()->getFileName(), $tmp);
        FileSystem::delete($tmpFileName);

        return count($this->res);
    }

    private function where()
    {
        $up     = $this->query->getUpdateData();
        $wheres = $this->query->getWhereCondition();
        $rows   = $this->query->getTable()->getRows();
        $res    = $rows;

        foreach ($wheres as $where) {
            foreach ($rows as $rowNumber => $row) {
                foreach ($row as $column => $value) {
                    if ($where['column'] === $column) {
                        
                        if ($where['operator'] === '=') {
                            if ($where['value'] === $value) {                                                        
                                foreach ($up as $upKey => $upValue) {
                                    $res[$rowNumber][$upKey] = $upValue;
                                }
                            }
                        }

                        if ($where['operator'] === '>') {
                            if ($where['value'] > $value) {
                                foreach ($up as $upKey => $upValue) {
                                    $res[$rowNumber][$upKey] = $upValue;
                                }
                            }
                        }

                        if ($where['operator'] === '>=') {
                            if ($where['value'] >= $value) {
                                foreach ($up as $upKey => $upValue) {
                                    $res[$rowNumber][$upKey] = $upValue;
                                }
                            }
                        }

                        if ($where['operator'] === '<') {
                            if ($where['value'] < $value) {
                                foreach ($up as $upKey => $upValue) {
                                    $res[$rowNumber][$upKey] = $upValue;
                                }
                            }
                        }

                        if ($where['operator'] === '<=') {
                            if ($where['value'] <= $value) {
                                foreach ($up as $upKey => $upValue) {
                                    $res[$rowNumber][$upKey] = $upValue;
                                }
                            }
                        }

                        if ($where['operator'] === '!=' || $where['operator'] === '<>') {
                            if ($where['value'] !== $value) {
                                foreach ($up as $upKey => $upValue) {
                                    $res[$rowNumber][$upKey] = $upValue;
                                }
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

