<?php

namespace pql\QueryExecutor;

use Exception;
use Nette\Utils\FileSystem;
use pql\Condition;
use pql\QueryBuilder\DeleteQuery as DeleteBuilder;
use pql\Table;
use SplFileObject;

class DeleteExecutor implements IQueryExecutor
{
    use LimitExecutor;

    private DeleteBuilder $query;

    private array $result;

    public function __construct(DeleteBuilder $query)
    {
        $this->query = $query;
    }

    public function run(): int
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

    private function doWhere(array $rows, Condition $condition): array
    {
        foreach ($rows as $rowNumber => $row) {
            if (ConditionHelper::condition($condition, $row, [])) {
                unset($rows[$rowNumber]);
            }
        }

        return $this->result = $rows;
    }

    private function where(): void
    {
        $rows = $this->query->getTable()->getRows();

        foreach ($this->query->getWhereConditions() as $whereCondition) {
            $rows = $this->doWhere($rows, $whereCondition);
        }

        $this->result = $rows;
    }
}
