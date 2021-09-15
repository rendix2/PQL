<?php
/**
 *
 * Created by PhpStorm.
 * Filename: UnionAllExecutor.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 23:09
 */

namespace PQL\Query\Runner;

use PQL\Query\Builder\Select;

class UnionAllExecutor implements IExecutor
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
        if (!count($this->query->getUnionedAllQueries())) {
            return $rows;
        }

        foreach ($this->query->getUnionedAllQueries() as $unionedAllQuery) {
            $unionedAllRows = $unionedAllQuery->execute();

            $rows = array_merge($rows, $unionedAllRows);
        }

        return $rows;
    }
}