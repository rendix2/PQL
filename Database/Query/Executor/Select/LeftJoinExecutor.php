<?php
/**
 *
 * Created by PhpStorm.
 * Filename: LeftjoinExecutor.php
 * User: Tomáš Babický
 * Date: 23.09.2021
 * Time: 10:18
 */

namespace PQL\Database\Query\Executor\Select;

use Nette\NotImplementedException;
use PQL\Database\Query\Builder\Expressions\JoinCondition;
use PQL\Database\Query\Builder\Expressions\WhereCondition;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Executor\IExecutor;
use PQL\Database\Query\Executor\JoinHelper;
use PQL\Database\Query\Executor\WhereExecutor;
use PQL\Database\Query\Optimizer\LeftJoinOptimizer;
use PQL\Database\Query\Scheduler\Scheduler;

/**
 * Class LeftJoinExecutor
 *
 * @package PQL\Database\Query\Executor\Select
 */
class LeftJoinExecutor implements IExecutor
{
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
     * @var LeftJoinOptimizer $leftJoinOptimizer
     */
    private LeftJoinOptimizer $leftJoinOptimizer;

    /**
     * @var WhereExecutor $whereExecutor
     */
    private WhereExecutor $whereExecutor;

    /**
     * @param SelectBuilder     $query
     * @param Scheduler         $scheduler
     * @param LeftJoinOptimizer $leftJoinOptimizer
     * @param WhereExecutor     $whereExecutor
     * @param JoinHelper        $joinHelper
     */
    public function __construct(
        SelectBuilder $query,
        Scheduler $scheduler,
        LeftJoinOptimizer $leftJoinOptimizer,
        WhereExecutor $whereExecutor,
        JoinHelper $joinHelper,
    ) {
        $this->query = $query;
        $this->scheduler = $scheduler;
        $this->leftJoinOptimizer = $leftJoinOptimizer;
        $this->whereExecutor = $whereExecutor;
        $this->joinHelper = $joinHelper;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    public function run(array $rows) : array
    {
        if (!$this->scheduler->hasLeftJoins()) {
            return $rows;
        }

        $returnRows = $rows;

        foreach ($this->query->getLeftJoinedTables() as $i => $leftJoinedTable) {
            $nullEntity = $leftJoinedTable->getJoinExpression()->getNullEntity();

            foreach ($leftJoinedTable->getJoinConditions() as $j => $joinCondition) {
                $schedulerJoin = $this->scheduler->getLeftJoins()[$i][$j];

                $tableRows = $this->joinHelper->getData($schedulerJoin, $leftJoinedTable, $joinCondition, $rows);

                if ($joinCondition instanceof JoinCondition) {
                    $returnRows = $this->leftJoinOptimizer->leftJoin(
                        $schedulerJoin->getJoin(),
                        $rows,
                        $tableRows,
                        $joinCondition,
                        $nullEntity
                    );
                } elseif ($joinCondition instanceof WhereCondition) {
                    $returnRows = $this->whereExecutor->innerWhere($rows, $joinCondition);
                } else {
                    throw new NotImplementedException();
                }
            }
        }

        return $returnRows;
    }
}