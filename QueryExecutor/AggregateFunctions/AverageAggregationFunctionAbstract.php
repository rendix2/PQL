<?php

namespace pql\QueryExecutor\AggregateFunctions;

class AverageAggregationFunctionAbstract extends AbstractAggregateFunction
{
    public function run(string $column, string $functionColumnName): void
    {
        $functionGroupByResult  = [];

        foreach ($this->getQuery()->getGroupedByData() as $groupByColumn => $groupByRows) {
            foreach ($groupByRows as $groupByValue => $groupedRows) {
                foreach ($groupedRows as $groupedRow) {
                    if (isset($functionGroupByResult[$groupByColumn][$groupByValue])) {
                        $functionGroupByResult[$groupByColumn][$groupByValue] += $groupedRow[$column];
                    } else {
                        $functionGroupByResult[$groupByColumn][$groupByValue] = $groupedRow[$column];
                    }
                }

                $functionGroupByResult[$groupByColumn][$groupByValue] /= count($groupedRows);
            }

            $this->getQuery()->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
        }
    }
}