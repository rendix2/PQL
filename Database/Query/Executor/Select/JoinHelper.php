<?php
/**
 *
 * Created by PhpStorm.
 * Filename: JoinExecutor.php
 * User: Tomáš Babický
 * Date: 05.09.2021
 * Time: 0:30
 */

namespace PQL\Database\Query\Executor;

use Exception;
use PQL\Database\Query\Builder\Expressions\Column;
use PQL\Database\Query\Builder\Expressions\ICondition;
use PQL\Database\Query\Builder\Expressions\IValue;
use PQL\Database\Query\Builder\JoinExpression;
use PQL\Database\Query\Scheduler\JoinScheduler;
use PQL\Database\Query\Scheduler\Scheduler;

/**
 * Class JoinHelper
 *
 * @package PQL\Database\Query\Executor
 */
class JoinHelper
{
    /**
     * @param JoinScheduler  $schedulerJoin
     * @param JoinExpression $joinedTable
     * @param ICondition     $joinCondition
     * @param array          $rows
     *
     * @return array
     * @throws Exception
     */
    public function getData(
        JoinScheduler  $schedulerJoin,
        JoinExpression $joinedTable,
        ICondition     $joinCondition,
        array          $rows
    ) : array {
        if ($schedulerJoin->getDataSource() === Scheduler::TABLE) {
            $tableRows = $joinedTable->getJoinExpression()->getData();
        } elseif ($schedulerJoin->getDataSource() === Scheduler::INDEX) {
            $table = $joinedTable->getJoinExpression()->getTable();
            $primaryKey = $table->getMetaData()->primaryTableKey;

            $left = $joinCondition->getLeft()->evaluate();
            $right = $joinCondition->getRight()?->evaluate();

            if ($joinCondition->getLeft() instanceof Column && $joinCondition->getRight() instanceof Column) {
                if (isset($rows[0]->{$left})) {
                    $values = array_column($rows, $left);
                } elseif (isset($rows[0]->{$right})) {
                    $values = array_column($rows, $right);
                } else {
                    throw new Exception('Column data not found');
                }
            } elseif ($joinCondition->getLeft() instanceof IValue || $joinCondition->getRight() instanceof IValue) {
                if ($joinCondition->getLeft() instanceof IValue) {
                    $values = $left;
                } elseif ($joinCondition->getRight() instanceof IValue) {
                    $values = $right;
                } else {
                    throw new Exception('Unknown input...');
                }
            }

            $values = array_unique($values);

            $primaryIndex = $table->getPrimaryIndex();

            $tableRows = [];

            foreach ($values as $value) {
                $searchedRow = $primaryIndex->searchKey($primaryKey, $value);

                if ($searchedRow !== false) {
                    $tableRows[] = $searchedRow;
                }
            }
        } else {
            throw new Exception('Unknown join plan');
        }

        return $tableRows;
    }
}