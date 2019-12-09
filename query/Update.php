<?php
namespace query;

use Nette\Utils\FileSystem;
use SplFileObject;
use Table;

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
            $file->fwrite(implode(Table::COLUMN_DELIMITER, $values) . $this->query->getTable()->getFileEnds());
        }
        
        $file = null;
        $tmp = file_get_contents($tmpFileName);

        file_put_contents($this->query->getTable()->getFilePath(), $tmp);
        FileSystem::delete($tmpFileName);

        return count($this->result);
    }

    /**
     * @param array      $rows
     * @param \Condition $condition
     * @param array      $up
     *
     * @return array
     */
    private function doWhere(array $rows, \Condition $condition, array $up)
    {
        foreach ($rows as $rowNumber => $row) {
            if (ConditionHelper::condition($condition, $row, [])) {
                foreach ($up as $upKey => $upValue) {
                    $rows[$rowNumber][$upKey] = $upValue;
                }
            }
        }

        return $rows;
    }

    /**
     * where conditions
     */
    private function where()
    {
        $up     = $this->query->getUpdateData();
        $whereConditions = $this->query->getWhereCondition();
        $rows   = $this->query->getTable()->getRows();

        foreach ($whereConditions as $whereCondition) {
            $rows = $this->doWhere($rows, $whereCondition, $up);
        }

        $this->result = $rows;
    }
}

