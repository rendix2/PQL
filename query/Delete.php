<?php
namespace query;

use Condition;
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
        
        foreach ($this->result as $row) {
            $fileTemp->fwrite(implode(Table::COLUMN_DELIMITER, $row) . $this->query->getTable()->getFileEnds());
        }
        
        $fileTemp = null;        
        $tmpFile  = file_get_contents($tmpFileName);

        file_put_contents($this->query->getTable()->getFilePath(), $tmpFile);
                
        FileSystem::delete($tmpFileName);

        return count($this->result);
    }

    /**
     * @param array     $rows
     * @param Condition $condition
     *
     * @return array
     */
    private function doWhere(array $rows, Condition $condition)
    {
        foreach ($rows as $rowNumber => $row) {
            if (ConditionHelper::condition($condition, $row, [])) {
                unset($rows[$rowNumber]);
            }
        }

        return $this->result = $rows;
    }

    /**
     *
     */
    private function where()
    {
        $rows = $this->query->getTable()->getRows();

        foreach ($this->query->getWhereConditions() as $whereCondition) {
            $rows = $this->doWhere($rows, $whereCondition);
        }

        $this->result = $rows;
    }
}

