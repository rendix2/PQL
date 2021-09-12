<?php
/**
 *
 * Created by PhpStorm.
 * Filename: WhereExecutor.php
 * User: Tomáš Babický
 * Date: 05.09.2021
 * Time: 0:13
 */

namespace PQL\Query\Runner;


use PQL\Query\Builder\Expressions\WhereCondition;
use PQL\Query\Builder\Select;

class WhereExecutor implements IExecutor
{

    private Select $query;

    private ConditionExecutor $conditionExecutor;

    /**
     * WhereExecutor constructor.
     *
     * @param Select            $query
     * @param ConditionExecutor $conditionExecutor
     */
    public function __construct(Select $query, ConditionExecutor $conditionExecutor)
    {
        $this->query = $query;
        $this->conditionExecutor = $conditionExecutor;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function run(array $rows) : array
    {
        foreach ($this->query->getWhereConditions() as $whereCondition) {
            $rows = $this->innerWhere($rows, $whereCondition);
        }

        return $rows;
    }

    public function innerWhere(array $rows, WhereCondition $whereCondition) : array
    {
        $validRows = [];

        foreach ($rows as $row) {
            if ($this->conditionExecutor->where($row, $whereCondition)) {
                $validRows[] = $row;
            }
        }

        return $validRows;
    }
}