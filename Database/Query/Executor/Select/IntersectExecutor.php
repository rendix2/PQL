<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IntersectExecutor.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 23:07
 */

namespace PQL\Database\Query\Executor;

use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Scheduler\Scheduler;

/**
 * Class IntersectExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class IntersectExecutor implements IExecutor
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

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
        if (!$this->scheduler->hasIntersectClause()) {
            return $rows;
        }

        $resultRows = [];

        foreach ($this->query->getIntersected() as $intersectedQuery) {
            $intersectedRows = $intersectedQuery->execute();

            foreach ($intersectedRows as $intersectedRow) {
                foreach ($rows as $row) {
                    if ((array)$row === (array)$intersectedRow) {
                        $resultRows[] = $row;
                    }
                }
            }
        }

        return $resultRows;
    }
}