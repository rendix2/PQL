<?php
/**
 *
 * Created by PhpStorm.
 * Filename: CrossJoinExecutor.php
 * User: Tomáš Babický
 * Date: 23.09.2021
 * Time: 10:19
 */

namespace PQL\Database\Query\Executor\Select;

use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Executor\IExecutor;
use PQL\Database\Query\Executor\Join\NestedLoopJoin;

/**
 * Class CrossJoinExecutor
 *
 * @package PQL\Database\Query\Executor\Select
 */
class CrossJoinExecutor implements IExecutor
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @var NestedLoopJoin $nestedLoopJoin
     */
    private NestedLoopJoin $nestedLoopJoin;

    /**
     * @param SelectBuilder  $query
     * @param NestedLoopJoin $nestedLoopJoin
     */
    public function __construct(SelectBuilder $query, NestedLoopJoin $nestedLoopJoin)
    {
        $this->query = $query;
        $this->nestedLoopJoin = $nestedLoopJoin;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    public function run(array $rows) : array
    {
        foreach ($this->query->getCrossJoinedTables() as $crossJoinedTable) {
            $tableRows = $crossJoinedTable->getJoinExpression()->getData();

            $rows = $this->nestedLoopJoin->crossJoin($rows, $tableRows);
        }

        return $rows;
    }
}