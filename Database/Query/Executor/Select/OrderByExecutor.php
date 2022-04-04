<?php
/**
 *
 * Created by PhpStorm.
 * Filename: OrderByExecutor.php
 * User: Tomáš Babický
 * Date: 05.09.2021
 * Time: 1:29
 */

namespace PQL\Database\Query\Executor;

use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Scheduler\Scheduler;
use PQL\Query\ArrayHelper;

/**
 * Class OrderByExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class OrderByExecutor implements IExecutor
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
     * OrderByExecutor constructor.
     *
     * @param SelectBuilder $query
     * @param Scheduler     $scheduler
     */
    public function __construct(
        SelectBuilder $query,
        Scheduler $scheduler,
    ) {
        $this->query = $query;
        $this->scheduler = $scheduler;
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
     */
    public function run(array $rows) : array
    {
/*        if (!$this->scheduler->hasOrderByClause()) {
            return $rows;
        }*/

        $resultTemp = ArrayHelper::toArray($rows);

        $sortTemp = [];

        foreach ($this->query->getOrderByColumns() as $orderByColumn) {
            $columnName = $orderByColumn->getExpression()->evaluate();

            $sortTemp[] = array_column($resultTemp, $columnName);
            $sortTemp[] = $orderByColumn->getSortingConst();
            $sortTemp[] = SORT_REGULAR;
        }

        $sortTemp[] = &$resultTemp;

        array_multisort(...$sortTemp); // lets make the hard work of sorting

        return ArrayHelper::toObject($resultTemp);
    }
}
