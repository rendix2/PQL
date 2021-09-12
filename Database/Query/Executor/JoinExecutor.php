<?php
/**
 *
 * Created by PhpStorm.
 * Filename: JoinExecutor.php
 * User: Tomáš Babický
 * Date: 05.09.2021
 * Time: 0:30
 */

namespace PQL\Query\Runner;


use PQL\Query\Builder\Expressions\JoinConditionExpression;
use PQL\Query\Builder\Expressions\WhereCondition;
use PQL\Query\Builder\Select;

class JoinExecutor
{

    private Optimizer $optimizer;

    private Select $query;

    private WhereExecutor $whereExecutor;

    /**
     * JoinExecutor constructor.
     *
     * @param Select        $query
     * @param Optimizer     $optimizer
     * @param WhereExecutor $whereExecutor
     */
    public function __construct(Select $query, Optimizer $optimizer, WhereExecutor $whereExecutor)
    {
        $this->optimizer = $optimizer;
        $this->query = $query;
        $this->whereExecutor = $whereExecutor;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function leftJoins(array $rows) : array
    {
        foreach ($this->query->getLeftJoinedTables() as $leftJoinedTable) {
            $tableRows = $leftJoinedTable->getJoinExpression()->getData();
            $entity = $leftJoinedTable->getJoinExpression()->getNullEntity();

            foreach ($leftJoinedTable->getJoinConditions() as $joinCondition) {
                if ($joinCondition instanceof JoinConditionExpression) {
                    $rows = $this->optimizer->leftJoin($rows, $tableRows, $joinCondition, $entity);
                } elseif ($joinCondition instanceof WhereCondition) {
                    $rows = $this->whereExecutor->innerWhere($rows, $joinCondition);
                }
            }
        }

        return $rows;
    }

    public function rightJoins(array $rows)
    {

    }

    public function innerJoins(array $rows) : array
    {
        foreach ($this->query->getInnerJoinedTables() as $innerJoinedTable) {
            $tableRows = $innerJoinedTable->getJoinExpression()->getData();

            foreach ($innerJoinedTable->getJoinConditions() as $joinCondition) {
                if ($joinCondition instanceof JoinConditionExpression) {
                    $rows = $this->optimizer->innerJoin($rows, $tableRows, $joinCondition);
                } elseif ($joinCondition instanceof WhereCondition) {
                    $rows = $this->whereExecutor->innerWhere($rows, $joinCondition);
                }
            }
        }

        return $rows;
    }

    public function crossJoins(array $rows) : array
    {
        foreach ($this->query->getCrossJoinedTables() as $crossJoinedTable) {
            $tableRows = $crossJoinedTable->getJoinExpression()->getData();

            $rows = $this->optimizer->crossJoin($rows, $tableRows);
        }

        return $rows;
    }



}