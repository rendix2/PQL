<?php

namespace pql\QueryExecutor\AggregateFunctions;

/**
 * Class Count
 *
 * @package pql\QueryExecutor\AggregateFunctions
 */
class Count extends AggregateFunction
{
    /**
     * @inheritDoc
     */
    public function run($column, $functionColumnName)
    {
        $functionGroupByResult = [];

        foreach ($this->getQuery()->getGroupedByData() as $groupedByColumn => $groupByRows) {
            foreach ($groupByRows as $groupByValue => $groupedRows) {
                $functionGroupByResult[$groupedByColumn][$groupByValue] = count($groupedRows);
            }

            $this->getQuery()->addGroupedFunctionDataIntoResult(
                $groupedByColumn,
                $functionGroupByResult,
                $functionColumnName
            );
        }
    }
}