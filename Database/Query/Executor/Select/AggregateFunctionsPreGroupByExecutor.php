<?php
/**
 *
 * Created by PhpStorm.
 * Filename: AgregateFunctionsExecuter.php
 * User: Tomáš Babický
 * Date: 02.09.2021
 * Time: 0:48
 */

namespace PQL\Database\Query\Executor;

use Exception;
use PQL\Database\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Database\Query\Builder\SelectBuilder;

/**
 * Class AggregateFunctionsPreGroupByExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class AggregateFunctionsPreGroupByExecutor implements IExecutor
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @var array $rows
     */
    private array $rows;

    /**
     * @param SelectBuilder $query
     */
    public function __construct(SelectBuilder $query)
    {
        $this->query = $query;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @throws Exception
     */
    public function run(array $rows) : array
    {
        $this->rows = $rows;

        foreach ($this->query->getAggregateFunctions() as $function) {
            $lowerName = $function->getLowerName();

            if (method_exists($this, $lowerName)) {
                $functionResult = $this->{$lowerName}($function);

                $this->setValue($function, $functionResult);
            } else {
                $message = sprintf('Function "%s" does not exist.', $lowerName);

                throw new Exception($message);
            }
        }

        return $this->rows;
    }

    private function setValue(AggregateFunctionExpression $function, float $value) : void
    {
        foreach ($this->rows as $row) {
            $row->{$function->evaluate()} = $value;
        }
    }

    private function avg(AggregateFunctionExpression $function) : float
    {
        $argument = $function->getArguments()[0]->evaluate();
        $columnValues = array_column($this->rows, $argument);
        return array_sum($columnValues) / count($this->rows);
    }

    private function sum(AggregateFunctionExpression $function) : float
    {
        $argument = $function->getArguments()[0]->evaluate();
        $columnValues = array_column($this->rows, $argument);

        return array_sum($columnValues);
    }

    private function min(AggregateFunctionExpression $function) : float
    {
        $argument = $function->getArguments()[0]->evaluate();
        $columnValues = array_column($this->rows, $argument);

        return min($columnValues);
    }

    private function max(AggregateFunctionExpression $function) : float
    {
        $argument = $function->getArguments()[0]->evaluate();
        $columnValues = array_column($this->rows, $argument);

        return max($columnValues);
    }

    private function count(AggregateFunctionExpression $function) : float
    {
        $argument = $function->getArguments()[0]->evaluate();
        $columnValues = array_column($this->rows, $argument);

        return count($columnValues);
    }
}