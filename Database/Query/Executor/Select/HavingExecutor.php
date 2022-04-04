<?php
/**
 *
 * Created by PhpStorm.
 * Filename: HavingExecutor.php
 * User: Tomáš Babický
 * Date: 04.09.2021
 * Time: 14:28
 */

namespace PQL\Database\Query\Executor;

use Exception;
use Nette\InvalidArgumentException;
use PQL\Database\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Database\Query\Builder\Expressions\Division;
use PQL\Database\Query\Builder\Expressions\HavingCondition;
use PQL\Database\Query\Builder\Expressions\IMathBinaryOperator;
use PQL\Database\Query\Builder\Expressions\IMathExpression;
use PQL\Database\Query\Builder\Expressions\Minus;
use PQL\Database\Query\Builder\Expressions\Multiplication;
use PQL\Database\Query\Builder\Expressions\Plus;
use PQL\Database\Query\Builder\Expressions\Power;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Scheduler\Scheduler;
use PQL\Database\Query\Select\Condition\HavingConditionExecutor;

/**
 * Class HavingExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class HavingExecutor implements IExecutor
{
    /**
     * @var HavingConditionExecutor $havingConditionExecutor
     */
    private HavingConditionExecutor $havingConditionExecutor;

    /**
     * @var GroupByExecutor $groupByExecutor
     */
    private GroupByExecutor $groupByExecutor;

    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @var Scheduler $scheduler
     */
    private Scheduler $scheduler;

    /**
     * HavingExecutor constructor.
     *
     * @param SelectBuilder           $query
     * @param Scheduler               $scheduler
     * @param GroupByExecutor         $groupByExecutor
     * @param HavingConditionExecutor $havingConditionExecutor
     */
    public function __construct(
        SelectBuilder           $query,
        Scheduler               $scheduler,
        GroupByExecutor         $groupByExecutor,
        HavingConditionExecutor $havingConditionExecutor
    ) {
        $this->query = $query;
        $this->scheduler = $scheduler;
        $this->groupByExecutor = $groupByExecutor;
        $this->havingConditionExecutor = $havingConditionExecutor;
    }

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
        if (!$this->scheduler->hasHavingClause()) {
            return $rows;
        }

        $returnRows = [];

        foreach ($this->query->getHavingConditions() as $havingCondition) {
            $left = $havingCondition->getLeft();

            if ($left instanceof AggregateFunctionExpression) {
                $lowerName = $left->getLowerName();

                if (method_exists($this, $lowerName)) {
                    $returnRows = $this->{$lowerName}($havingCondition);
                } else {
                    $message = sprintf('Function "%s" does not exist.', $lowerName);

                    throw new Exception($message);
                }
            } elseif ($left instanceof IMathBinaryOperator) {
                if ($left->getLeft() instanceof AggregateFunctionExpression) {
                    $lowerName = $left->getLeft()->getUpperName();
                } elseif ($left->getRight() instanceof AggregateFunctionExpression) {
                    $lowerName = $left->getRight()->getUpperName();
                } else {
                    throw new Exception('Unknown input');
                }

                if (method_exists($this, $lowerName)) {
                    $returnRows = $this->{$lowerName}($havingCondition);
                } else {
                    $message = sprintf('Function "%s" does not exist.', $lowerName);

                    throw new Exception($message);
                }
            } else {
                throw new Exception('Unknown input.');
            }
        }

        return $returnRows;
    }

    /**
     * @param HavingCondition $havingCondition
     *
     * @return string|null
     * @throws Exception
     */
    private function processArgument(HavingCondition $havingCondition) : ?string
    {
        $argument = null;

        $leftCondition = $havingCondition->getLeft();

        // find out argument
        if ($leftCondition instanceof AggregateFunctionExpression) {
            $argument = $leftCondition->getArguments()[0]->evaluate();
        } elseif ($leftCondition instanceof IMathBinaryOperator) {
            if ($leftCondition->getLeft() instanceof AggregateFunctionExpression) {
                $argument = $leftCondition->getLeft()->getArguments()[0]->evaluate();
            } elseif ($leftCondition->getRight() instanceof AggregateFunctionExpression) {
                $argument = $leftCondition->getRight()->getArguments()[0]->evaluate();
            } else {
                throw new Exception('Unknown input.');
            }
        }

        return $argument;
    }

    /**
     * @param HavingCondition $havingCondition
     * @param float           $value
     *
     * @return float
     * @throws Exception
     */
    private function processMathExpressions(HavingCondition $havingCondition, float $value) : float
    {
        $leftCondition = $havingCondition->getLeft();

        if ($leftCondition instanceof Plus) {
            if ($leftCondition->getLeft() instanceof AggregateFunctionExpression) {
                $value += $leftCondition->getRight()->evaluate();
            } elseif ($leftCondition->getRight() instanceof AggregateFunctionExpression) {
                $value += $leftCondition->getLeft()->evaluate();
            }
        } elseif ($leftCondition instanceof Minus) {
            if ($leftCondition->getLeft() instanceof AggregateFunctionExpression) {
                $value -= $leftCondition->getRight()->evaluate();
            } elseif ($leftCondition->getRight() instanceof AggregateFunctionExpression) {
                $value = $leftCondition->getLeft()->evaluate() - $value;
            }
        } elseif ($leftCondition instanceof Multiplication) {
            if ($leftCondition->getLeft() instanceof AggregateFunctionExpression) {
                $value *= $leftCondition->getRight()->evaluate();
            } elseif ($leftCondition->getRight() instanceof AggregateFunctionExpression) {
                $value *= $leftCondition->getLeft()->evaluate();
            }
        } elseif ($leftCondition instanceof Division) {
            if ($leftCondition->getLeft() instanceof AggregateFunctionExpression) {
                $value /= $leftCondition->getRight()->evaluate();
            } elseif ($leftCondition->getRight() instanceof AggregateFunctionExpression) {
                if ($value === 0.0) {
                    throw new Exception('Division by zero.');
                }

                $value = (float) $leftCondition->getLeft()->evaluate() / $value;
            }
        } elseif ($leftCondition instanceof Power) {
            if ($leftCondition->getLeft() instanceof AggregateFunctionExpression) {
                $value **= $leftCondition->getRight()->evaluate();
            } elseif ($leftCondition->getRight() instanceof AggregateFunctionExpression) {
                $value **= $leftCondition->getLeft()->evaluate();
            }
        }

        return $value;
    }

    /**
     * @param HavingCondition $havingCondition
     *
     * @return array
     * @throws Exception
     */
    private function count(HavingCondition $havingCondition) : array
    {
        $argument = $this->processArgument($havingCondition);

        if (!isset($this->groupByExecutor->getGroupedData()[$argument])) {
            $message = sprintf('We are not using GROUP BY %s', $argument);

            throw new Exception($message);
        }

        $rows = [];

        foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedRows) {
            $count = $this->processMathExpressions($havingCondition, count($groupedRows));

            if ($this->havingConditionExecutor->run($count, $havingCondition)) {
                $rows = array_merge($rows, $groupedRows);
            }
        }

        return $rows;
    }

    /**
     * @param HavingCondition $havingCondition
     *
     * @return array
     * @throws Exception
     */
    private function max(HavingCondition $havingCondition) : array
    {
        $argument = $this->processArgument($havingCondition);

        if (!isset($this->groupByExecutor->getGroupedData()[$argument])) {
            $message = sprintf('We are not using GROUP BY %s', $argument);

            throw new Exception($message);
        }

        $rows = [];

        foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedRows) {
            $columnValues = array_column($groupedRows, $argument);
            $max = $this->processMathExpressions($havingCondition, max($columnValues));

            if ($this->havingConditionExecutor->run($max, $havingCondition)) {
                $rows = array_merge($rows, $groupedRows);
            }
        }

        return $rows;
    }

    /**
     * @param HavingCondition $havingCondition
     *
     * @return array
     * @throws Exception
     */
    private function min(HavingCondition $havingCondition) : array
    {
        $argument = $this->processArgument($havingCondition);

        if (!isset($this->groupByExecutor->getGroupedData()[$argument])) {
            $message = sprintf('We are not using GROUP BY %s', $argument);

            throw new Exception($message);
        }

        $rows = [];

        foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedRows) {
            $columnValues = array_column($groupedRows, $argument);
            $min = $this->processMathExpressions($havingCondition, min($columnValues));

            if ($this->havingConditionExecutor->run($min, $havingCondition)) {
                $rows = array_merge($rows, $groupedRows);
            }
        }

        return $rows;
    }

    /**
     * @param HavingCondition $havingCondition
     *
     * @return array
     * @throws Exception
     */
    private function sum(HavingCondition $havingCondition) : array
    {
        $argument = $this->processArgument($havingCondition);

        if (!isset($this->groupByExecutor->getGroupedData()[$argument])) {
            $message = sprintf('We are not using GROUP BY %s', $argument);

            throw new Exception($message);
        }

        $rows = [];

        foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedRows) {
            $columnValues = array_column($groupedRows, $argument);
            $sum = $this->processMathExpressions($havingCondition, array_sum($columnValues));

            if ($this->havingConditionExecutor->run($sum, $havingCondition)) {
                $rows = array_merge($rows, $groupedRows);
            }
        }

        return $rows;
    }

    /**
     * @param HavingCondition $havingCondition
     *
     * @return array
     * @throws Exception
     */
    private function avg(HavingCondition $havingCondition) : array
    {
        $argument = $this->processArgument($havingCondition);

        if (!isset($this->groupByExecutor->getGroupedData()[$argument])) {
            $message = sprintf('We are not using GROUP BY %s', $argument);

            throw new Exception($message);
        }

        $rows = [];

        foreach ($this->groupByExecutor->getGroupedData()[$argument] as $groupedRows) {
            $columnValues = array_column($groupedRows, $argument);
            $avg = $this->processMathExpressions($havingCondition, array_sum($columnValues) / count($columnValues));

            if ($this->havingConditionExecutor->run($avg, $havingCondition)) {
                $rows = array_merge($rows, $groupedRows);
            }
        }

        return $rows;
    }
}
