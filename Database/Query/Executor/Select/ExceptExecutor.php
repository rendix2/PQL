<?php
/**
 *
 * Created by PhpStorm.
 * Filename: ExceptExecutor.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 23:08
 */

namespace PQL\Database\Query\Executor;

use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Scheduler\Scheduler;
use PQL\Query\ArrayHelper;

/**
 * Class ExceptExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class ExceptExecutor implements IExecutor
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
    public function run(array $rows): array
    {
        if (!$this->scheduler->hasExceptClause()) {
            return $rows;
        }

        $resultRows = [];

        foreach ($this->query->getExceptedQueries() as $exceptedQuery) {
            $exceptedRowsTmp = $exceptedQuery->execute();
            $exceptedRows = ArrayHelper::toArray($exceptedRowsTmp);

            foreach ($rows as $row) {
                if (!in_array((array)$row, $exceptedRows, true)) {
                    $resultRows[] = $row;
                }
            }
        }

        return $resultRows;
    }
}
