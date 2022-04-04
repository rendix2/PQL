<?php
/**
 *
 * Created by PhpStorm.
 * Filename: DeleteExecutor.php
 * User: Tomáš Babický
 * Date: 21.09.2021
 * Time: 21:40
 */

namespace PQL\Query;

use PQL\Database\Query\Builder\DeleteBuilder;
use PQL\Database\Query\Select\Condition\WhereConditionExecutor;
use PQL\Database\Storage\IStorage;
use PQL\Database\Storage\StandardStorage;

/**
 * Class DeleteExecutor
 *
 * @package PQL\Query
 */
class DeleteExecutor
{
    /**
     * @var DeleteBuilder $query
     */
    private DeleteBuilder $query;

    /**
     * @var IStorage $storage
     */
    private IStorage $storage;

    /**
     * @var WhereConditionExecutor $whereConditionExecutor
     */
    private WhereConditionExecutor $whereConditionExecutor;

    /**
     * @param DeleteBuilder $query
     */
    public function __construct(DeleteBuilder $query)
    {
        $this->query = $query;
        $this->storage = new StandardStorage($this->query->getTable()->getTable());

        $this->whereConditionExecutor = new WhereConditionExecutor();
    }

    /**
     * @return bool
     */
    public function run() : bool
    {
        $rows = $this->storage->getAllTableData();

        $deletedRows = [];

        if (count($this->query->getWhereConditions())) {
            foreach ($rows as $i => $row) {
                foreach ($this->query->getWhereConditions() as $whereCondition) {
                    if ($this->whereConditionExecutor->run($row, $whereCondition)) {
                        $deletedRows[] = $row;
                        unset($rows[$i]);
                    }
                }
            }

        } else {
            $rows = [];
        }

        return $this->storage->delete($rows, $deletedRows);
    }
}
