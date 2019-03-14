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

        $tmpFileName = $this->query->getTable()->getFileName() . '.tmp';

        $file = new SplFileObject($tmpFileName,'a');
        /*

        $file->next();
        $line = 1;
        while (!$file->eof()) {
            $textLine = implode(Table::COLUMN_DELIMITER, $this->res[$line]);

            if ($textLine !== $file->current()) {
                $length = mb_strlen($file->current());

                $file->fseek(-$length, SEEK_CUR);
                $file->fwrite($textLine, $length);
            }
        }
*/
        
        $file->fwrite($this->query->getTable()->getColumnsString());
        
        foreach ($this->result as $values) {
            $file->fwrite(implode(Table::COLUMN_DELIMITER, $values) . "\n");
        }
        
        $file = null;
        
        $tmp = file_get_contents($tmpFileName);
        file_put_contents($this->query->getTable()->getFileName(), $tmp);
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

