<?php
/**
 *
 * Created by PhpStorm.
 * Filename: HavingExecutor.php
 * User: Tomáš Babický
 * Date: 04.09.2021
 * Time: 14:28
 */

namespace PQL\Query\Runner;


use Exception;
use PQL\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Query\Builder\Expressions\HavingCondition;
use PQL\Query\Builder\Expressions\WhereCondition;
use PQL\Query\Builder\Select;

class HavingExecutor implements IExecutor
{
    private AggregateFunctionsPreGroupByExecutor $aggregateFunctionsExecutor;

    private ConditionExecutor $conditionExecutor;

    private GroupByExecutor $groupByExecutor;

    private Select $query;

    /**
     * HavingExecutor constructor.
     *
     * @param Select                               $query
     * @param AggregateFunctionsPreGroupByExecutor $aggregateFunctionsExecutor
     * @param GroupByExecutor                      $groupByExecutor
     */
    public function __construct(
        Select                               $query,
        AggregateFunctionsPreGroupByExecutor $aggregateFunctionsExecutor,
        GroupByExecutor                      $groupByExecutor,
        ConditionExecutor                    $conditionExecutor
    ) {
        $this->aggregateFunctionsExecutor = $aggregateFunctionsExecutor;
        $this->query = $query;
        $this->groupByExecutor = $groupByExecutor;

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
        if (!count($this->query->getHavingConditions())) {
            return $rows;
        }

        $returnRows = [];

        foreach ($this->query->getHavingConditions() as $havingCondition) {
            $left = $havingCondition->getLeft();

            if ($left instanceof AggregateFunctionExpression) {
                if (method_exists($this, mb_strtoupper($left->getName()))) {
                    $returnRows = $this->{mb_strtoupper($left->getName())}($havingCondition);
                } else {
                    $message = sprintf('Function "%s" does not exist.', mb_strtolower($left->getName()));

                    throw new Exception($message);
                }
            }
        }

        return $returnRows;
    }

    private function count(HavingCondition $havingCondition) : array
    {
        $argument = $havingCondition->getLeft()->getArguments()[0]->evaluate();

        if (!isset($this->groupByExecutor->getGroupedData()[$argument])) {
            $message = sprintf('We are not using GROUP BY %s', $argument);

            throw new Exception($message);
        }

        $rows = [];

        foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedRows) {
            if ($this->conditionExecutor->having(count($groupedRows), $havingCondition)) {
                $rows = array_merge($rows, $groupedRows);
            }
        }

        return $rows;
    }

    private function max(HavingCondition $havingCondition) : array
    {
        $argument = $havingCondition->getLeft()->getArguments()[0]->evaluate();

        if (!isset($this->groupByExecutor->getGroupedData()[$argument])) {
            $message = sprintf('We are not using GROUP BY %s', $argument);

            throw new Exception($message);
        }

        $rows = [];

        if (isset($this->groupByExecutor->getGroupedData()[$argument])) {
            foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedRows) {
                $values = array_column($groupedRows, $argument);

                if ($this->conditionExecutor->having(max($values), $havingCondition)) {
                    $rows = array_merge($rows, $groupedRows);
                }
            }
        }

        return $rows;
    }

    private function min(HavingCondition $havingCondition) : array
    {
        $argument = $havingCondition->getLeft()->getArguments()[0]->evaluate();

        if (!isset($this->groupByExecutor->getGroupedData()[$argument])) {
            $message = sprintf('We are not using GROUP BY %s', $argument);

            throw new Exception($message);
        }

        $rows = [];

        if (isset($this->groupByExecutor->getGroupedData()[$argument])) {
            foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedRows) {
                $values = array_column($groupedRows, $argument);

                if ($this->conditionExecutor->having(min($values), $havingCondition)) {
                    $rows = array_merge($rows, $groupedRows);
                }
            }
        }

        return $rows;
    }

    private function sum(HavingCondition $havingCondition) : array
    {
        $argument = $havingCondition->getLeft()->getArguments()[0]->evaluate();

        if (!isset($this->groupByExecutor->getGroupedData()[$argument])) {
            $message = sprintf('We are not using GROUP BY %s', $argument);

            throw new Exception($message);
        }

        $rows = [];

        if (isset($this->groupByExecutor->getGroupedData()[$argument])) {
            foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedRows) {
                $values = array_column($groupedRows, $argument);

                if ($this->conditionExecutor->having(array_sum($values), $havingCondition)) {
                    $rows = array_merge($rows, $groupedRows);
                }
            }
        }

        return $rows;
    }

    private function avg(HavingCondition $havingCondition) : array
    {
        $argument = $havingCondition->getLeft()->getArguments()[0]->evaluate();

        if (!isset($this->groupByExecutor->getGroupedData()[$argument])) {
            $message = sprintf('We are not using GROUP BY %s', $argument);

            throw new Exception($message);
        }

        $rows = [];

        if (isset($this->groupByExecutor->getGroupedData()[$argument])) {
            foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedRows) {
                $values = array_column($groupedRows, $argument);
                $avg = array_sum($values) / count($values);

                if ($this->conditionExecutor->having($avg, $havingCondition)) {
                    $rows = array_merge($rows, $groupedRows);
                }
            }
        }

        return $rows;
    }
}
