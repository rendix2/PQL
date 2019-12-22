<?php
namespace query;

use Condition;
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
     * @param Condition $condition
     * @param array      $updateData
     *
     * @return array
     */
    private function doWhere(array $rows, Condition $condition, array $updateData)
    {
        foreach ($rows as $rowNumber => $row) {
            if (ConditionHelper::condition($condition, $row, [])) {
                foreach ($updateData as $upKey => $upValue) {
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
        $updateData = $this->query->getUpdateData();
        $rows = $this->query->getTable()->getRows();

        foreach ($this->query->getWhereConditions() as $whereCondition) {
            $rows = $this->doWhere($rows, $whereCondition, $updateData);
        }

        $this->result = $rows;
    }
}
