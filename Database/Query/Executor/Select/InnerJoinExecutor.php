<?php
/**
 *
 * Created by PhpStorm.
 * Filename: InnerJoinExecutor.php
 * User: Tomáš Babický
 * Date: 23.09.2021
 * Time: 10:19
 */

namespace PQL\Database\Query\Executor\Select;

use PQL\Database\Query\Builder\Expressions\JoinCondition;
use PQL\Database\Query\Builder\Expressions\WhereCondition;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Executor\IExecutor;
use PQL\Database\Query\Executor\JoinHelper;
use PQL\Database\Query\Executor\WhereExecutor;
use PQL\Database\Query\Optimizer\InnerJoinOptimizer;
use PQL\Database\Query\Scheduler\Scheduler;

/**
 * Class InnerJoinExecutor
 *
 * @package PQL\Database\Query\Executor\Select
 */
class InnerJoinExecutor implements IExecutor
{
    /**
     * @var InnerJoinOptimizer $innerJoinOptimizer
     */
    private InnerJoinOptimizer $innerJoinOptimizer;

    /**
     * @var JoinHelper $joinHelper
     */
    private JoinHelper $joinHelper;

    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @var Scheduler $scheduler
     */
    private Scheduler $scheduler;

    /**
     * @var WhereExecutor $whereExecutor
     */
    private WhereExecutor $whereExecutor;

    /**
     * @param SelectBuilder      $query
     * @param Scheduler          $scheduler
     * @param WhereExecutor      $whereExecutor
     * @param InnerJoinOptimizer $innerJoinOptimizer
     * @param JoinHelper         $joinHelper
     */
    public function __construct(
        SelectBuilder      $query,
        Scheduler          $scheduler,
        WhereExecutor      $whereExecutor,
        InnerJoinOptimizer $innerJoinOptimizer,
        JoinHelper         $joinHelper
    ) {
        $this->query = $query;
        $this->innerJoinOptimizer = $innerJoinOptimizer;
        $this->joinHelper = $joinHelper;
        $this->scheduler = $scheduler;
        $this->whereExecutor = $whereExecutor;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    public function run(array $rows) : array
    {
        if (!$this->scheduler->hasInnerJoins()) {
            return $rows;
        }

        foreach ($this->query->getInnerJoinedTables() as $i => $innerJoinedTable) {
            foreach ($innerJoinedTable->getJoinConditions() as $j => $joinCondition) {
                $schedulerJoins = $this->scheduler->getInnerJoins();
                $schedulerJoin = $schedulerJoins[$i][$j];

                $tableRows = $this->joinHelper->getData($schedulerJoin, $innerJoinedTable, $joinCondition, $rows);

                if ($joinCondition instanceof JoinCondition) {
                    $rows = $this->innerJoinOptimizer->innerJoin($schedulerJoin->getJoin(), $rows, $tableRows, $joinCondition);
                } elseif ($joinCondition instanceof WhereCondition) {
                    $rows = $this->whereExecutor->innerWhere($rows, $joinCondition);
                }
            }
        }

        return $rows;
    }
}