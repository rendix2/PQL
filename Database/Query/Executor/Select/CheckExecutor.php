<?php
/**
 *
 * Created by PhpStorm.
 * Filename: CheckExecutor.php
 * User: Tomáš Babický
 * Date: 22.01.2022
 * Time: 1:17
 */

namespace PQL\Database\Query\Executor;

use Exception;
use PQL\Database\Query\Builder\Expressions\Column;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Scheduler\Scheduler;

/**
 * Class CheckExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class CheckExecutor implements IExecutor
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
     * CheckExecutor constructor
     *
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
     * CheckExecutor destructor.
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
        $this->checkColumnCount();
        $this->checkColumnExists();
        $this->checkDistinct();


        $this->checkWhereColumns();
        $this->checkOrderByColumns();

        $this->checkAggregateFunctionsArgument();

        return [];
    }

    /**
     * @throws Exception
     */
    private function checkColumnExists() : void
    {
        foreach ($this->query->getColumns() as $column) {
            if ($column instanceof Column) {
                $columnExists = $column->getTable()->checkColumnExists($column);

               if (!$columnExists) {
                   $message = sprintf('Column "%s" in table "%s" does not exists.', $column->getName(), $column->getTable()->getName());

                   throw new Exception($message);
               }
            }
        }
    }

    /**
     * @throws Exception
     */
    private function checkDistinct() : void
    {
        if ($this->scheduler->hasDistinct()) {
            if ($this->scheduler->getCountColumns() > 1) {
                throw new Exception('We are using Distinct and have more than one selected columns.');
            }

            if (($this->scheduler->getCountColumns() === 1) && $this->query->getDistinct() !== $this->query->getColumns()[0]) {
                throw new Exception('We have set Distinct column and columns. If distinct column is used, you cannot use normal columns.');
            }
        }
    }

    /**
     * @throws Exception
     */
    private function checkAggregateFunctionsArgument() : void
    {
        foreach ($this->query->getAggregateFunctions() as $aggregationFunction) {
            $argumentColumns = $aggregationFunction->getArguments()[0];

            if ($argumentColumns instanceof Column) {
                $tableColumns = $argumentColumns->getTable()->getColumns();

                foreach ($tableColumns as $tableColumn) {
                    if (($tableColumn->name === $argumentColumns->getName()) && !($tableColumn->type === 'int' || $tableColumn->type === 'float')) {
                        $message = sprintf(
                            'Column "%s" of table "%s" used in "%s" is not numeric (INT or FLOAT).',
                            $tableColumn->name,
                            $argumentColumns->getTable()->getName(),
                            $aggregationFunction->getUpperName()
                        );

                        throw new Exception($message);
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    private function checkColumnCount() : void
    {
        if (!$this->scheduler->getCountColumns()) {
            $message = sprintf('Your query has no selected columns. Please select at least one column in SELECT clause.');

            throw new Exception($message);
        }
    }

    /**
     * @throws Exception
     */
    private function checkWhereColumns() : void
    {
        foreach ($this->query->getWhereConditions() as $whereCondition) {
            if ($whereCondition->getLeft() instanceof Column) {
                $column = $whereCondition->getLeft();

                $columnExists = $column->getTable()->checkColumnExists($column);

                if (!$columnExists) {
                    $message = sprintf('Column "%s" in table "%s" does not exists.', $column->getName(), $column->getTable()->getName());

                    throw new Exception($message);
                }
            }

            if ($whereCondition->getRight() instanceof Column) {
                $column = $whereCondition->getRight();

                $columnExists = $column->getTable()->checkColumnExists($column);

                if (!$columnExists) {
                    $message = sprintf('Column "%s" in table "%s" does not exists.', $column->getName(), $column->getTable()->getName());

                    throw new Exception($message);
                }
            }
        }
    }

    private function checkOrderByColumns() : void
    {
        foreach ($this->query->getOrderByColumns() as $orderByExpression) {
            if ($orderByExpression->getExpression() instanceof Column) {
                $column = $orderByExpression->getExpression();

                $columnExists = $column->getTable()->checkColumnExists($column);

                if (!$columnExists) {
                    $message = sprintf('Column "%s" in table "%s" does not exists.', $column->getName(), $column->getTable()->getName());

                    throw new Exception($message);
                }
            }
        }
    }
}
