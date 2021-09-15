<?php
/**
 *
 * Created by PhpStorm.
 * Filename: AgregateFunctionsExecuter.php
 * User: Tomáš Babický
 * Date: 02.09.2021
 * Time: 0:48
 */

namespace PQL\Query\Runner;


use Exception;
use PQL\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Query\Builder\Select;

class AggregateFunctionsPreGroupByExecutor implements IExecutor
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

    private function setValue(AggregateFunctionExpression $function, mixed $value): array
    {
        foreach ($this->rows as $row) {
            $row->{$function->evaluate()} = $value;
        }

        return $this->rows;
    }

    private function avg(AggregateFunctionExpression $function): array
    {
        $argument = $function->getArguments()[0]->evaluate();
        $array_column = array_column($this->rows, $argument);
        $avg = array_sum($array_column) / $this->rowsCount;

        return $this->setValue($function, $avg);
    }

    private function sum(AggregateFunctionExpression $function): array
    {
        $argument = $function->getArguments()[0]->evaluate();
        $array_column = array_column($this->rows, $argument);
        $sum = array_sum($array_column);

        return $this->setValue($function, $sum);
    }

    private function min(AggregateFunctionExpression $function): array
    {
        $argument = $function->getArguments()[0]->evaluate();
        $array_column = array_column($this->rows, $argument);
        $min = min($array_column);

        return $this->setValue($function, $min);
    }

    private function max(AggregateFunctionExpression $function): array
    {
        $argument = $function->getArguments()[0]->evaluate();
        $array_column = array_column($this->rows, $argument);
        $max = max($array_column);

        return $this->setValue($function, $max);
    }

    private function count(AggregateFunctionExpression $function): array
    {
        $argument = $function->getArguments()[0]->evaluate();
        $array_column = array_column($this->rows, $argument);
        $count = count($array_column);

        return $this->setValue($function, $count);
    }
}