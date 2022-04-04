<?php
/**
 *
 * Created by PhpStorm.
 * Filename: AggregateFunctooonPostGroupBys.php
 * User: Tomáš Babický
 * Date: 12.09.2021
 * Time: 2:22
 */

namespace PQL\Database\Query\Executor;

use Exception;
use PQL\Database\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Database\Query\Builder\Expressions\IMathExpression;
use PQL\Database\Query\Builder\SelectBuilder;

/**
 * Class AggregateFunctionsPostGroupByExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class AggregateFunctionsPostGroupByExecutor implements IExecutor
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @var GroupByExecutor $groupByExecutor
     */
    private GroupByExecutor $groupByExecutor;

    /**
     * @var array $rows
     */
    private array $rows;

    /**
     * AggregateFunctionsPostGroupByExecutor constructor
     *
     * @param SelectBuilder   $query
     * @param GroupByExecutor $groupByExecutor
     */
    public function __construct(SelectBuilder $query, GroupByExecutor $groupByExecutor)
    {
        $this->query = $query;
        $this->groupByExecutor = $groupByExecutor;
    }

    /**
     * AggregateFunctionsPostGroupByExecutor destructor.
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
     * @throws Exception
     */
    public function run(array $rows) : array
    {
        $this->rows = $rows;

        // calculate for aggregate functions
        foreach ($this->query->getAggregateFunctions() as $function) {
            $lowerName = $function->getLowerName();

            if (method_exists($this, $lowerName)) {
                $rows = $this->{$lowerName}($function);
            } else {
                $message = sprintf('Function "%s" does not exist.', $lowerName);

                throw new Exception($message);
            }
        }

        return $rows;
    }

    /**
     * @param AggregateFunctionExpression $function
     *
     * @return array
     */
    private function avg(AggregateFunctionExpression $function) : array
    {
        $argument = $function->getArguments()[0]->evaluate();

        // is grouped by this column
        if (isset($this->groupByExecutor->getGroupedData()[$argument])) {
            // hack: we have it already grouped by $argument, we can use only its value
            foreach ($this->rows as $row) {
                $row->{$function->evaluate()} = $row->{$argument};
            }
        }

        return $this->rows;
    }

    /**
     * @param AggregateFunctionExpression $function
     *
     * @return array
     */
    public function sum(AggregateFunctionExpression $function) : array
    {
        $argument = $function->getArguments()[0]->evaluate();

        // is grouped by this column
        if (isset($this->groupByExecutor->getGroupedData()[$argument])) {
            $extra = [];

            foreach ($this->rows as $i => $row) {
                $columnValues = array_column($this->groupByExecutor->getGroupedData()[$argument][$row->{$argument}], $argument);

                $row->{$function->evaluate()} = array_sum($columnValues);
            }

            foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedValue => $groupedRows) {
                foreach ($groupedRows as $groupedRow) {
                    $columnValues = array_column($this->groupByExecutor->getGroupedData()[$argument][$groupedRow->{$argument}], $argument);
                    $sum = array_sum($columnValues);

                    $groupedRow->{$function->evaluate()} = $sum;
                    $extra[$groupedValue] = $sum;
                }
            }

            foreach ($this->rows as $row) {
                foreach ($extra as $key => $value) {
                    if ($row->{$argument} === $key) {
                        $row->{$function->print()} = $value;
                        break;
                    }
                }
            }
        }

        return $this->rows;
    }

    /**
     * @param AggregateFunctionExpression $function
     *
     * @return array
     */
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

    /**
     * @param AggregateFunctionExpression $function
     *
     * @return array
     */
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

    /**
     * @param AggregateFunctionExpression $function
     *
     * @return array
     */
    private function count(AggregateFunctionExpression $function) : array
    {
        $argument = $function->getArguments()[0]->evaluate();

        // is grouped by this column
        if (isset($this->groupByExecutor->getGroupedData()[$argument])) {
            $extra = [];

            foreach ($this->rows as $row) {
                $count = count($this->groupByExecutor->getGroupedData()[$argument][$row->{$argument}]);

                $row->{$function->evaluate()} = $count;

            }

            foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedValue => $groupedRows) {
                $columnValues = array_column($groupedRows, $argument);
                $count = count($columnValues);

                foreach ($groupedRows as $groupedRow) {
                    $groupedRow->{$function->evaluate()} = $count;
                    $extra[$groupedValue] = $count;
                }
            }

            foreach ($this->rows as $row) {
                foreach ($extra as $key => $value) {
                    if ($row->{$argument} === $key) {
                        $row->{$function->print()} = $value;
                        break;
                    }
                }
            }
        }

        return $this->rows;
    }
}
