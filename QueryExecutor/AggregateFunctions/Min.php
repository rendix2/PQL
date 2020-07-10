<?php

namespace pql\QueryExecutor\AggregateFunctions;

/**
 * Class Min
 *
 * @package pql\QueryExecutor\AggregateFunctions
 */
class Min extends AggregateFunction
{
    /**
     * @inheritDoc
     */
    public function run($column, $functionColumnName)
    {
        $functionGroupByResult  = [];

        foreach ($this->getQuery()->getGroupedByData() as $groupByColumn => $groupByRows) {
            foreach ($groupByRows as $groupByValue => $groupedRows) {
                foreach ($groupedRows as $groupedRow) {
                    if (!isset($functionGroupByResult[$groupByColumn][$groupByValue])) {
                        $functionGroupByResult[$groupByColumn][$groupByValue] = INF;
                    }

                    if ($groupedRow[$column] < $functionGroupByResult[$groupByColumn][$groupByValue]) {
                        $functionGroupByResult[$groupByColumn][$groupByValue]= $groupedRow[$column];
                    }
                }
            }

            $this->getQuery()->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
        }
    }
}