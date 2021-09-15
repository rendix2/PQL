<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IntersectExecutor.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 23:07
 */

namespace PQL\Query\Runner;

use PQL\Query\Builder\Select;

class IntersectExecutor implements IExecutor
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
        if (!count($this->query->getIntersected())) {
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