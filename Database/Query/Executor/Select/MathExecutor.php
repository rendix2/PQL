<?php
/**
 *
 * Created by PhpStorm.
 * Filename: MathExecutor.php
 * User: Tomáš Babický
 * Date: 10.01.2022
 * Time: 16:08
 */

namespace PQL\Database\Query\Executor;

use Exception;
use PQL\Database\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Database\Query\Builder\Expressions\Division;
use PQL\Database\Query\Builder\Expressions\IExpression;
use PQL\Database\Query\Builder\Expressions\IMathBinaryOperator;
use PQL\Database\Query\Builder\Expressions\IMathExpression;
use PQL\Database\Query\Builder\Expressions\Minus;
use PQL\Database\Query\Builder\Expressions\Multiplication;
use PQL\Database\Query\Builder\Expressions\Plus;
use PQL\Database\Query\Builder\Expressions\Power;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Scheduler\Scheduler;

/**
 * Class MathExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class MathExecutor implements IExecutor
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
     * @param Scheduler     $scheduler
     */
    public function __construct(
        SelectBuilder $query,
        Scheduler $scheduler
    ) {
        $this->query = $query;
        $this->scheduler = $scheduler;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    public function run(array $rows) : array
    {
        if (!$this->scheduler->hasMathBinaryOperators()) {
            return $rows;
        }

        foreach ($this->query->getMathBinaryOperators() as $column) {
            if ($column->getLeft() instanceof AggregateFunctionExpression) {
                $rows = $this->process($column, $rows);
            } elseif ($column->getRight() instanceof AggregateFunctionExpression) {
                $rows = $this->process($column, $rows);
            }
        }

        return $rows;
    }

    /**
     * @param IMathBinaryOperator $column
     * @param array               $rows
     *
     * @return array
     * @throws Exception
     */
    private function process(IMathBinaryOperator $column, array $rows) : array
    {
        foreach ($rows as $row) {
            $function = $row->{$column->getLeft()->print()};

            if ($column instanceof Plus) {
                $res = $function + $column->getRight()->evaluate();
            } elseif ($column instanceof Minus) {
                $res = $function - $column->getRight()->evaluate();
            } elseif ($column instanceof Multiplication) {
                $res = $function * $column->getRight()->evaluate();
            } elseif ($column instanceof Division) {
                $res = $function / $column->getRight()->evaluate();
            } elseif ($column instanceof Power) {
                $res = $function ** $column->getRight()->evaluate();
            } else {
                throw new Exception('Unknown input.');
            }

            $row->{$column->print()} = $res;
        }

        return $rows;
    }
}
