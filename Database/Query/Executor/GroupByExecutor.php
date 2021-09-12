<?php
/**
 *
 * Created by PhpStorm.
 * Filename: GroupByExecutor.php
 * User: Tomáš Babický
 * Date: 01.09.2021
 * Time: 16:55
 */

namespace PQL\Query\Runner;


use PQL\Query\Builder\Expressions\Column;
use PQL\Query\Builder\Select;

class GroupByExecutor implements IExecutor
{
    private Select $query;

    private array $groupedData;

    public function __construct(Select $select)
    {
        $this->query = $select;
        $this->groupedData = [];
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function run(array $rows) : array
    {
        $groupedData = [];

        foreach ($this->query->getGroupByColumns() as $column) {
            $groupedData[$column->getTableColumnName()] = $rows = $this->makeGroups($rows, $column);
            $rows = $this->groupByColumn($rows);
        }

        $this->groupedData = $groupedData;

        return $rows;
    }

    private function makeGroups(array $data, Column $column) : array
    {
        $groupByData = [];

        foreach ($data as $row) {
            $groupByData[$row->{$column->getTableColumnName()}][] = $row;
        }

        return $groupByData;
    }

    private function groupByColumn(array $groupByData) : array
    {
        $tempResult = [];

        foreach ($groupByData as $row) {
            $tempResult[] = $row[0];
        }

        return $tempResult;
    }

    public function getGroupedData() : array
    {
        return $this->groupedData;
    }
}