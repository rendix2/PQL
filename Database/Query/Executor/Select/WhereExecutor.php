<?php
/**
 *
 * Created by PhpStorm.
 * Filename: WhereExecutor.php
 * User: Tomáš Babický
 * Date: 05.09.2021
 * Time: 0:13
 */

namespace PQL\Database\Query\Executor;

use PQL\Database\Query\Builder\Expressions\WhereCondition;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Scheduler\Scheduler;
use PQL\Database\Query\Select\Condition\WhereConditionExecutor;

/**
 * Class WhereExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class WhereExecutor implements IExecutor
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @var WhereConditionExecutor $whereConditionExecutor
     */
    private WhereConditionExecutor $whereConditionExecutor;

    /**
     * @var Scheduler $scheduler
     */
    private Scheduler $scheduler;

    /**
     * WhereExecutor constructor.
     *
     * @param SelectBuilder          $query
     * @param Scheduler              $scheduler
     * @param WhereConditionExecutor $whereConditionExecutor
     */
    public function __construct(
        SelectBuilder $query,
        Scheduler $scheduler,
        WhereConditionExecutor $whereConditionExecutor,
    ) {
        $this->query = $query;
        $this->scheduler = $scheduler;
        $this->whereConditionExecutor = $whereConditionExecutor;
    }

    /**
     * WhereExecutor destructor.
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    public function run(array $rows) : array
    {
        if (!$this->scheduler->hasWhereClause()) {
            return $rows;
        }

        foreach ($this->query->getWhereConditions() as $whereCondition) {
            $rows = $this->innerWhere($rows, $whereCondition);
        }

        return $rows;
    }

    /**
     * @param array          $rows
     * @param WhereCondition $whereCondition
     *
     * @return array
     */
    public function innerWhere(array $rows, WhereCondition $whereCondition) : array
    {
        $validRows = [];

        foreach ($rows as $row) {
            if ($this->whereConditionExecutor->run($row, $whereCondition)) {
                $validRows[] = $row;
            }
        }

        return $validRows;
    }
}
