<?php
/**
 *
 * Created by PhpStorm.
 * Filename: ExplainExecutor.php
 * User: Tomáš Babický
 * Date: 22.10.2021
 * Time: 12:20
 */

namespace PQL\Query;

use PQL\Database\Query\Builder\Expressions\QueryExpression;
use PQL\Database\Query\Builder\Expressions\TableExpression;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Scheduler\Scheduler;
use ReflectionClass;

/**
 * Class ExplainExecutor
 *
 * @package PQL\Query
 */
class ExplainExecutor
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @var Scheduler $scheduler
     */
    private Scheduler $scheduler;

    /**
     * @param SelectBuilder $query
     */
    public function __construct(SelectBuilder $query)
    {
        $this->query = $query;

        $container = new Container($query);

        $this->scheduler = $container->getScheduler();
    }

    /**
     * @return array
     */
    public function run() : array
    {
        return array_merge(
            $this->from(),
            $this->innerJoins(),
            $this->crossJoins(),
            $this->leftJoins(),
            $this->rightJoins()
        );
    }

    /**
     * @return array
     */
    public function from() : array
    {
        $rows = [];

        $fromClause = $this->query->getFromClause();

        if ($fromClause instanceof TableExpression) {
            $rows['from'] = [
                'table' => $fromClause->getTable()->getName(),
                'rows' => $fromClause->getTable()->getMetaData()->rowsCount,
                'object' => 'table',
            ];
        } elseif ($fromClause instanceof QueryExpression) {
            $explainExecutor = new ExplainExecutor($fromClause->getQuery());
            $explainRows = $explainExecutor->run();

            $rows = array_merge($rows, $explainRows);
        }

        return $rows;
    }

    /**
     * @return array
     */
    private function innerJoins() : array
    {
        $joinExpressions = $this->query->getInnerJoinedTables();

        $rows = [];
        $conditionsRows = [];

        foreach ($joinExpressions as $i => $joinExpression) {
            $realJoinedExpression = $joinExpression->getJoinExpression();

            foreach ($joinExpression->getJoinConditions() as $j => $condition) {
                $join = $this->scheduler->getInnerJoins()[$i][$j];

                $algorithm = $join->getJoin();
                $source = $join->getDataSource();
                $sourceString = $source === Scheduler::INDEX ? 'INDEX' : 'TABLE';
                $reflection = new ReflectionClass($algorithm);

                $conditionsRows[] = [
                    'operator' => $condition->getOperator()->getOperator(),
                    'source' => $sourceString,
                    'algorithm' => $reflection->getShortName(),
                ];

                if ($realJoinedExpression instanceof TableExpression) {
                    $rows['innerJoins'][$i] = [
                        'table' => $realJoinedExpression->getTable()->getName(),
                        'rows' => $realJoinedExpression->getTable()->getMetaData()->rowsCount,
                        'object' => 'table',
                        'conditions' => $conditionsRows,
                    ];
                }
            }
        }

        return $rows;
    }

    /**
     * @return array
     */
    private function crossJoins() : array
    {
        $joinExpressions = $this->query->getCrossJoinedTables();

        $rows = [];

        foreach ($joinExpressions as $i => $joinExpression) {
            $realJoinedExpression = $joinExpression->getJoinExpression();

            if ($realJoinedExpression instanceof TableExpression) {
                $rows['crossJoins'][$i] = [
                    'table' => $realJoinedExpression->getTable()->getName(),
                    'rows' => $realJoinedExpression->getTable()->getMetaData()->rowsCount,
                    'object' => 'table',
                    'source' => 'TABLE',
                ];
            }
        }

        return $rows;
    }

    /**
     * @return array
     */
    private function leftJoins() : array
    {
        $joinExpressions = $this->query->getLeftJoinedTables();

        $rows = [];
        $conditionsRows = [];

        foreach ($joinExpressions as $i => $joinExpression) {
            $realJoinedExpression = $joinExpression->getJoinExpression();

            foreach ($joinExpression->getJoinConditions() as $j => $condition) {
                $join = $this->scheduler->getLeftJoins()[$i][$j];

                $algorithm = $join->getJoin();
                $source = $join->getDataSource();
                $sourceString = $source === Scheduler::INDEX ? 'INDEX' : 'TABLE';
                $reflection = new ReflectionClass($algorithm);

                $conditionsRows[] = [
                    'operator' => $condition->getOperator()->getOperator(),
                    'source' => $sourceString,
                    'algorithm' => $reflection->getShortName(),
                ];

                if ($realJoinedExpression instanceof TableExpression) {
                    $rows['leftJoins'][$i] = [
                        'table' => $realJoinedExpression->getTable()->getName(),
                        'rows' => $realJoinedExpression->getTable()->getMetaData()->rowsCount,
                        'object' => 'table',
                        'conditions' => $conditionsRows,
                    ];
                }
            }
        }

        return $rows;
    }

    /**
     * @return array
     */
    private function rightJoins() : array
    {
        $joinExpressions = $this->query->getRightJoinedTables();

        $rows = [];
        $conditionsRows = [];

        foreach ($joinExpressions as $i => $joinExpression) {
            $realJoinedExpression = $joinExpression->getJoinExpression();

            foreach ($joinExpression->getJoinConditions() as $j => $condition) {
                $join = $this->scheduler->getRightJoins()[$i][$j];

                $algorithm = $join->getJoin();
                $source = $join->getDataSource();
                $sourceString = $source === Scheduler::INDEX ? 'INDEX' : 'TABLE';
                $reflection = new ReflectionClass($algorithm);

                $conditionsRows[] = [
                    'operator' => $condition->getOperator()->getOperator(),
                    'source' => $sourceString,
                    'algorithm' => $reflection->getShortName(),
                ];

                if ($realJoinedExpression instanceof TableExpression) {
                    $rows['rightJoins'][$i] = [
                        'table' => $realJoinedExpression->getTable()->getName(),
                        'rows' => $realJoinedExpression->getTable()->getMetaData()->rowsCount,
                        'object' => 'table',
                        'conditions' => $conditionsRows,
                    ];
                }
            }
        }

        return $rows;
    }
}
