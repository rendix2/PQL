<?php
/**
 *
 * Created by PhpStorm.
 * Filename: UnionAllExecutor.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 23:09
 */

namespace PQL\Database\Query\Executor;

use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Scheduler\Scheduler;

/**
 * Class UnionAllExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class UnionAllExecutor implements IExecutor
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
        if (!$this->scheduler->hasUnionAllClause()) {
            return $rows;
        }

        foreach ($this->query->getUnionedAllQueries() as $unionedAllQuery) {
            $unionedAllRows = $unionedAllQuery->execute();

            $rows = array_merge($rows, $unionedAllRows);
        }

        return $rows;
    }
}
