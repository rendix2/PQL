<?php

namespace pql\QueryExecutor\AggregateFunctions;

/**
 * Class Median
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecutor\AggregateFunctions
 */
class Median extends AggregateFunction
{
    /**
     * @inheritDoc
     */
    public function run($column, $functionColumnName)
    {
        $functionGroupByResult  = [];

        $tmp = [];

        foreach ($this->getQuery()->getGroupedByData() as $groupByColumn => $groupByRows) {
            foreach ($groupByRows as $groupByValue => $groupedRows) {
                foreach ($groupedRows as $groupedRow) {
                    $tmp[$groupByColumn][$groupByValue][] = $groupedRow[$column];
                }

                sort($tmp[$groupByColumn][$groupByValue]);

                $count = count($tmp[$groupByColumn][$groupByValue]);
                $avgValue = $tmp[$groupByColumn][$groupByValue][$count / 2];

                if ($count % 2) {
                    $functionGroupByResult[$groupByColumn][$groupByValue] = $avgValue;
                } else {
                    $functionGroupByResult[$groupByColumn][$groupByValue]=
                        (
                            $avgValue + $tmp[$groupByColumn][$groupByValue][$count / 2 - 1]
                        ) / 2;
                }
            }

            $this->getQuery()->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
        }
    }
}