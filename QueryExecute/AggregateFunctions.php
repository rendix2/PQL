<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 13. 1. 2020
 * Time: 17:57
 */

namespace pql\QueryExecute;

/**
 * Class AggregateFunctions
 *
 * @author rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class AggregateFunctions
{
    /**
     * @var Select $query
     */
    private $query;

    /**
     * AggregateFunctions constructor.
     *
     * @param Select $query
     */
    public function __construct(Select $query)
    {
        $this->query = $query;
    }

    /**
     * AggregateFunctions destructor.
     */
    public function __destruct()
    {
        $this->query = null;
    }

    /**
     * @param string $column
     * @param string $functionColumnName
     */
    public function sum($column, $functionColumnName)
    {
        $functionGroupByResult = [];

        // iterate over grouped data!
        foreach ($this->query->getGroupedByData() as $groupByColumn => $groupByRows) {
            foreach ($groupByRows as $groupByValue => $groupedRows) {
                foreach ($groupedRows as $groupedRow) {
                    if (isset($functionGroupByResult[$groupByColumn][$groupByValue])) {
                        $functionGroupByResult[$groupByColumn][$groupByValue] += $groupedRow[$column];
                    } else {
                        $functionGroupByResult[$groupByColumn][$groupByValue] = $groupedRow[$column];
                    }
                }
            }

            $this->query->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
        }
    }

    /**
     * @param string $functionColumnName
     */
    public function count($functionColumnName)
    {
        $functionGroupByResult = [];

        foreach ($this->query->getGroupedByData() as $groupedByColumn => $groupByRows) {
            foreach ($groupByRows as $groupByValue => $groupedRows) {
                $functionGroupByResult[$groupedByColumn][$groupByValue] = count($groupedRows);
            }

            $this->query->addGroupedFunctionDataIntoResult(
                $groupedByColumn,
                $functionGroupByResult,
                $functionColumnName
            );
        }
    }

    /**
     * @param string $column
     * @param string $functionColumnName
     */
    public function average($column, $functionColumnName)
    {
        $functionGroupByResult  = [];

        foreach ($this->query->getGroupedByData() as $groupByColumn => $groupByRows) {
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

            $this->query->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
        }
    }

    /**
     * @param string $column
     * @param string $functionColumnName
     */
    public function min($column, $functionColumnName)
    {
        $functionGroupByResult  = [];

        foreach ($this->query->getGroupedByData() as $groupByColumn => $groupByRows) {
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

            $this->query->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
        }
    }

    /**
     * @param string $column
     * @param string $functionColumnName
     */
    public function max($column, $functionColumnName)
    {
        $functionGroupByResult  = [];

        foreach ($this->query->getGroupedByData() as $groupByColumn => $groupByRows) {
            foreach ($groupByRows as $groupByValue => $groupedRows) {
                foreach ($groupedRows as $groupedRow) {
                    if (!isset($functionGroupByResult[$groupByColumn][$groupByValue])) {
                        $functionGroupByResult[$groupByColumn][$groupByValue] = -INF;
                    }

                    if ($groupedRow[$column] > $functionGroupByResult[$groupByColumn][$groupByValue]) {
                        $functionGroupByResult[$groupByColumn][$groupByValue] = $groupedRow[$column];
                    }
                }
            }

            $this->query->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
        }
    }

    /**
     * @param string $column
     * @param string $functionColumnName
     */
    public function median($column, $functionColumnName)
    {
        $functionGroupByResult  = [];

        $tmp = [];

        foreach ($this->query->getGroupedByData() as $groupByColumn => $groupByRows) {
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

            $this->query->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
        }
    }
}
