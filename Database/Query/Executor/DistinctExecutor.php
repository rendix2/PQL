<?php
/**
 *
 * Created by PhpStorm.
 * Filename: DistinctExecutor.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 23:22
 */

namespace PQL\Query\Runner;

use PQL\Query\Builder\Select;

class DistinctExecutor implements IExecutor
{
    /**
     * @var Select $query
     */
    private Select $query;

    /**
     * @param Select $query
     */
    public function __construct(Select $query)
    {
        $this->query = $query;
    }

    public function run(array $rows): array
    {
        if (!$this->query->getDistinct()) {
            return $rows;
        }

        $rows = array_column($rows, $this->query->getDistinct()->evaluate());

        $distinctRowsTemp = array_unique($rows);
        $distinctRowsTempResult = [];

        foreach ($distinctRowsTemp as $distinctRow) {
            $row = [$this->query->getDistinct()->evaluate() => $distinctRow];

            $distinctRowsTempResult[] = (object)$row;
        }

        return $distinctRowsTempResult;
    }
}