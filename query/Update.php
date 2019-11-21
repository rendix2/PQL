<?php
namespace query;

use SplFileObject;
use Table;
use Nette\Utils\FileSystem;

/**
 * Class Update
 *
 * @author  rendix2
 * @package query
 */
class Update extends BaseQuery
{
    /**
     * run query
     */
    public function run()
    {
        $this->where();
        $this->limit();

        $tmpFileName = $this->query->getTable()->getFilePath() . '.tmp';

        $file = new SplFileObject($tmpFileName,'a');

        $file->fwrite($this->query->getTable()->getColumnsString() . $this->query->getTable()->getFileEnds());
        
        foreach ($this->result as $values) {
            $file->fwrite(implode(Table::COLUMN_DELIMITER, $values));
        }
        
        $file = null;
        $tmp = file_get_contents($tmpFileName);

        file_put_contents($this->query->getTable()->getFilePath(), $tmp);
        FileSystem::delete($tmpFileName);

        return count($this->result);
    }

    /**
     * where conditions
     */
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

        $this->result = $res;
    }
}

