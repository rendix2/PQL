<?php

namespace pql\QueryExecutor\AggregateFunctions;

class CountAggregationFunctionAbstract extends AbstractAggregateFunction
{
    public function run(string $column, string $functionColumnName): void
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