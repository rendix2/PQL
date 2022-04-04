<?php
/**
 *
 * Created by PhpStorm.
 * Filename: DistinctExecutor.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 23:22
 */

namespace PQL\Database\Query\Executor;

use PQL\Database\Query\Builder\SelectBuilder;

/**
 * Class DistinctExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class DistinctExecutor implements IExecutor
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @param SelectBuilder $query
     */
    public function __construct(SelectBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    public function run(array $rows): array
    {
        if (!$this->query->getDistinct()) {
            return $rows;
        }

        $columnValues = array_column($rows, $this->query->getDistinct()->evaluate());
        $distinctColumnValues = array_unique($columnValues);
        $distinctColumnValuesTempResult = [];

        foreach ($distinctColumnValues as $distinctColumnValue) {
            $row = [$this->query->getDistinct()->evaluate() => $distinctColumnValue];

            $distinctColumnValuesTempResult[] = (object)$row;
        }

        return $distinctColumnValuesTempResult;
    }
}