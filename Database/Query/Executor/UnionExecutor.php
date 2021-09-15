<?php
/**
 *
 * Created by PhpStorm.
 * Filename: UnionExecutor.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 23:09
 */

namespace PQL\Query\Runner;

use PQL\Query\Builder\Select;

class UnionExecutor implements IExecutor
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

    public function run(array $rows) : array
    {
        if (!count($this->query->getUnionedQueries())) {
            return $rows;
        }

        $tmpRows = [];

        foreach ($rows as $row) {
            $tmpRows[] = (array) $row;
        }

        foreach ($this->query->getUnionedQueries() as $unionedQuery) {
            $unionedRows = $unionedQuery->execute();

            foreach ($unionedRows as $unionedRow) {
                if (!in_array((array) $unionedRow, $tmpRows, true)) {
                    $tmpRows[] = $unionedRow;
                }
            }
        }

        $resultRows = [];

        foreach ($tmpRows as $row) {
            $resultRows[] = (object) $row;
        }

        return $resultRows;
    }
}