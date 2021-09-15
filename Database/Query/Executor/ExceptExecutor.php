<?php
/**
 *
 * Created by PhpStorm.
 * Filename: ExceptExecutor.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 23:08
 */

namespace PQL\Query\Runner;

use PQL\Query\Builder\Select;

class ExceptExecutor implements IExecutor
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
        if (!count($this->query->getExceptedQueries())) {
            return $rows;
        }

        $resultRows = [];

        foreach ($this->query->getExceptedQueries() as $exceptedQuery) {
            $exceptedRowsTmp = $exceptedQuery->execute();
            $exceptedRows = [];

            foreach ($exceptedRowsTmp as $exceptedRow) {
                $exceptedRows[] = (array) $exceptedRow;
            }

            foreach ($rows as $row) {
                if (!in_array((array)$row, $exceptedRows, true)) {
                    $resultRows[] = $row;
                }
            }
        }

        return $resultRows;
    }
}