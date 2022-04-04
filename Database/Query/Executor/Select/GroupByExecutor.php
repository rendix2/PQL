<?php
/**
 *
 * Created by PhpStorm.
 * Filename: GroupByExecutor.php
 * User: Tomáš Babický
 * Date: 01.09.2021
 * Time: 16:55
 */

namespace PQL\Database\Query\Executor;

use PQL\Database\Query\Builder\Expressions\Column;
use PQL\Database\Query\Builder\SelectBuilder;

/**
 * Class GroupByExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class GroupByExecutor implements IExecutor
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @var array $groupedData
     */
    private array $groupedData;

    /**
     * @param SelectBuilder $select
     */
    public function __construct(SelectBuilder $select)
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

    /**
     * @param array $rows
     *
     * @return array
     */
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

    /**
     * @param array  $data
     * @param Column $column
     *
     * @return array
     */
    private function makeGroups(array $data, Column $column) : array
    {
        $groupByData = [];

        foreach ($data as $row) {
            $groupByData[$row->{$column->getTableColumnName()}][] = $row;
        }

        return $groupByData;
    }

    /**
     * @param array $groupByData
     *
     * @return array
     */
    private function groupByColumn(array $groupByData) : array
    {
        $tempResult = [];

        foreach ($groupByData as $row) {
            $tempResult[] = $row[0];
        }

        return $tempResult;
    }

    /**
     * @return array
     */
    public function getGroupedData() : array
    {
        return $this->groupedData;
    }
}
