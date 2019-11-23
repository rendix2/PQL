<?php
namespace query;

use Exception;
use Nette\Utils\FileSystem;
use SplFileObject;
use Table;

class Delete extends BaseQuery
{
    /**
     * @return int
     * @throws Exception
     */
    public function run()
    {
        $this->where();
        $this->limit();

        $tmpFileName = $this->query->getTable()->getFilePath() . '.tmp';
        
        $fileTemp = new SplFileObject($tmpFileName, 'a');
        $fileTemp->fwrite($this->query->getTable()->getColumnsString() . $this->query->getTable()->getFileEnds());
        
        foreach ($this->query->getTable()->getRows() as $line => $row) {
            if (!in_array($line, $this->result, true)) {
                $fileTemp->fwrite(implode(Table::COLUMN_DELIMITER, $row));
            }
        }
        
        $fileTemp = null;        
        $tmpFile  = file_get_contents($tmpFileName);

        file_put_contents($this->query->getTable()->getFilePath(), $tmpFile);
                
        FileSystem::delete($tmpFileName);

        return count($this->result);
    }

    /**
     *
     */
    private function where()
    {
        $wheres = $this->query->getWhereCondition();
        $rows   = $this->query->getTable()->getRows();
        $result = [];

        foreach ($wheres as $where) {
            foreach ($rows as $rowNumber => $row) {
                foreach ($row as $column => $value) {
                    if ($where['column'] === $column) {
                        if ($where['operator'] === '=' && $where['value'] === $value) {
                          $result[] = $rowNumber;
                        }

                        if ($where['operator'] === '>' && $where['value'] > $value) {
                            $result[] = $rowNumber;
                        }

                        if ($where['operator'] === '>=' && $where['value'] >= $value) {
                            $result[] = $rowNumber;
                        }

                        if ($where['operator'] === '<' && $where['value'] < $value) {
                            $result[] = $rowNumber;
                        }

                        if ($where['operator'] === '<=' && $where['value'] <= $value) {
                            $result[] = $rowNumber;
                        }

                        if (($where['operator'] === '!=' || $where['operator'] === '<>') && $where['value'] !== $value) {
                            $result[] = $rowNumber;
                        }
                    }
                }
            }
        }

        $this->result = $result;
    }
}

