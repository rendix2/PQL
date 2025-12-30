<?php

namespace pql\QueryExecutor;

use Nette\Utils\FileSystem;
use pql\Condition;
use pql\QueryBuilder\UpdateQuery as UpdateBuilder;
use pql\Table;
use SplFileObject;

/**
 * Class Update
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class UpdateExecutor implements IQueryExecutor
{
    use LimitExecutor;

    private UpdateBuilder $query;

    private array $result;

    public function __construct(UpdateBuilder $query)
    {
        $this->query = $query;
    }


    /**
     * run query
     *
     * @return int
     */
    public function run()
    {
        $this->where();
        $this->limit();

        $tmpFileName = $this->query->getTable()->getFilePath() . '.tmp';

        $file = new SplFileObject($tmpFileName, 'a');

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

    private function doWhere(array $rows, Condition $condition, array $updateData): array
    {
        foreach ($rows as $rowNumber => $row) {
            if (ConditionHelper::condition($condition, $row, [])) {
                foreach ($updateData as $updateColumn => $updateValue) {
                    $rows[$rowNumber][$updateColumn] = $updateValue;
                }
            }
        }

        return $rows;
    }

    private function where(): void
    {
        $updateData = $this->query->getData();
        $rows = $this->query->getTable()->getRows();

        foreach ($this->query->getWhereConditions() as $whereCondition) {
            $rows = $this->doWhere($rows, $whereCondition, $updateData);
        }

        $this->result = $rows;
    }
}
