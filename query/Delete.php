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
        $whereConditions = $this->query->getWhereCondition();
        $rows            = $this->query->getTable()->getRows();
        $result          = [];

        foreach ($whereConditions as $whereCondition) {
            foreach ($rows as $rowNumber => $row) {
                foreach ($row as $column => $value) {
                    if ($whereCondition['column'] === $column) {
                        if ($whereCondition['operator'] === '=' && $whereCondition['value'] === $value) {
                          $result[] = $rowNumber;
                        }

                        if ($whereCondition['operator'] === '>' && $whereCondition['value'] > $value) {
                            $result[] = $rowNumber;
                        }

                        if ($whereCondition['operator'] === '>=' && $whereCondition['value'] >= $value) {
                            $result[] = $rowNumber;
                        }

                        if ($whereCondition['operator'] === '<' && $whereCondition['value'] < $value) {
                            $result[] = $rowNumber;
                        }

                        if ($whereCondition['operator'] === '<=' && $whereCondition['value'] <= $value) {
                            $result[] = $rowNumber;
                        }

                        if (($whereCondition['operator'] === '!=' || $whereCondition['operator'] === '<>') && $whereCondition['value'] !== $value) {
                            $result[] = $rowNumber;
                        }
                    }
                }
            }
        }

        $this->result = $result;
    }
}

