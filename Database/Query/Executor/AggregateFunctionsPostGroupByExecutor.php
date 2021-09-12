<?php
/**
 *
 * Created by PhpStorm.
 * Filename: AggregateFunctooonPostGroupBys.php
 * User: Tomáš Babický
 * Date: 12.09.2021
 * Time: 2:22
 */

namespace PQL\Query\Runner;

use Exception;
use PQL\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Query\Builder\Select;

class AggregateFunctionsPostGroupByExecutor implements IExecutor
{
    /**
     * @var Select $query
     */
    private Select $query;

    private GroupByExecutor $groupByExecutor;

    private array $rows;

    private int $rowsCount;

    public function __construct(Select $query, GroupByExecutor $groupByExecutor)
    {
        $this->query = $query;
        $this->groupByExecutor = $groupByExecutor;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function run(array $rows) : array
    {
        $this->rows = $rows;
        $this->rowsCount = count($this->rows);

        foreach ($this->query->getAggregateFunctions() as $function) {
            if (method_exists($this, mb_strtolower($function->getName()))) {
                $rows = $this->{mb_strtolower($function->getName())}($function);
            } else {
                $message = sprintf('Function "%s" does not exist.', mb_strtolower($function->getName()));

                throw new Exception($message);
            }
        }

        return $rows;
    }

    private function setValue(AggregateFunctionExpression $function, mixed $value) : array
    {
        foreach ($this->rows as $row) {
            $row->{$function->evaluate()} = $value;
        }

        return $this->rows;
    }

    private function avg(AggregateFunctionExpression $function) : array
    {
        $argument = $function->getArguments()[0]->evaluate();

        // is grouped by this column
        if (isset($this->groupByExecutor->getGroupedData()[$argument])) {
            // hack: we have it already grouped by $argument, we can ue only is value
            foreach ($this->rows as $row) {
                $row->{$function->evaluate()} = $row->{$argument};
            }
        }

        return $this->rows;
    }

    private function sum(AggregateFunctionExpression $function) : array
    {
        $argument = $function->getArguments()[0]->evaluate();

        // is grouped by this column
        if (isset($this->groupByExecutor->getGroupedData()[$argument])) {
            foreach ($this->rows as $row) {
                $columns = array_column($this->groupByExecutor->getGroupedData()[$argument][$row->{$argument}], $argument);

                $row->{$function->evaluate()} = array_sum($columns);
            }

            foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedValue => $groupedRows) {
                foreach ($groupedRows as $groupedRow) {
                    $columns = array_column($this->groupByExecutor->getGroupedData()[$argument][$groupedRow->{$argument}], $argument);

                    $groupedRow->{$function->evaluate()} = array_sum($columns);
                }
            }
        }

        return $this->rows;
    }

    private function min(AggregateFunctionExpression $function) : array
    {
        $argument = $function->getArguments()[0]->evaluate();

        // is grouped by this column
        if (isset($this->groupByExecutor->getGroupedData()[$argument])) {
            // hack: we have it already grouped by $argument, we can ue only is value
            foreach ($this->rows as $row) {
                $row->{$function->evaluate()} = $row->{$argument};
            }

            foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedValue => $groupedRows) {
                foreach ($groupedRows as $groupedRow) {
                    $groupedRow->{$function->evaluate()} = $groupedValue;
                }
            }
        }

        return $this->rows;
    }

    private function max(AggregateFunctionExpression $function) : array
    {
        $argument = $function->getArguments()[0]->evaluate();

        // is grouped by this column
        if (isset($this->groupByExecutor->getGroupedData()[$argument])) {
            // hack: we have it already grouped by $argument, we can ue only is value
            foreach ($this->rows as $row) {
                $row->{$function->evaluate()} = $row->{$argument};
            }

            foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedValue => $groupedRows) {
                foreach ($groupedRows as $groupedRow) {
                    $groupedRow->{$function->evaluate()} = $groupedValue;
                }
            }
        }

        return $this->rows;
    }

    private function count(AggregateFunctionExpression $function) : array
    {
        $argument = $function->getArguments()[0]->evaluate();

        // is grouped by this column
        if (isset($this->groupByExecutor->getGroupedData()[$argument])) {
            foreach ($this->rows as $row) {
                $count = count($this->groupByExecutor->getGroupedData()[$argument][$row->{$argument}]);

                $row->{$function->evaluate()} = $count;
            }

            foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedValue => $groupedRows) {
                $values = array_column($groupedRows, $argument);
                $count = count($values);

                foreach ($groupedRows as $groupedRow) {
                    $groupedRow->{$function->evaluate()} = $count;
                }
            }
        }

        return $this->rows;
    }
}