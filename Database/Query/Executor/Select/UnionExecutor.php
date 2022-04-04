<?php
/**
 *
 * Created by PhpStorm.
 * Filename: UnionExecutor.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 23:09
 */

namespace PQL\Database\Query\Executor;

use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Scheduler\Scheduler;
use PQL\Query\ArrayHelper;

/**
 * Class UnionExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class UnionExecutor implements IExecutor
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
        if (!$this->scheduler->hasUnionClause()) {
            return $rows;
        }

        $tmpRows = ArrayHelper::toArray($rows);

        foreach ($this->query->getUnionedQueries() as $unionedQuery) {
            $unionedRows = $unionedQuery->execute();

            foreach ($unionedRows as $unionedRow) {
                if (!in_array((array) $unionedRow, $tmpRows, true)) {
                    $tmpRows[] = $unionedRow;
                }
            }
        }

        return ArrayHelper::toObject($tmpRows);
    }
}
