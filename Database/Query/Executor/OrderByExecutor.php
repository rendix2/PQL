<?php
/**
 *
 * Created by PhpStorm.
 * Filename: OrderByExecutor.php
 * User: Tomáš Babický
 * Date: 05.09.2021
 * Time: 1:29
 */

namespace PQL\Query\Runner;


use PQL\Query\Builder\Select;

class OrderByExecutor implements IExecutor
{

    private Select $query;

    /**
     * OrderByExecutor constructor.
     *
     * @param Select $query
     */
    public function __construct(Select $query)
    {
        $this->query = $query;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function run(array $rows) : array
    {
        $resultTemp = [];

        foreach ($rows as $row) {
            $resultTemp[] = (array)$row;
        }

        $sortTemp = [];

        foreach ($this->query->getOrderByColumns() as $orderByColumn) {
            $columnName = $orderByColumn->getExpression()->evaluate();

            $sortTemp[] = array_column($resultTemp, $columnName);
            $sortTemp[] = $orderByColumn->getSortingConst();
            $sortTemp[] = SORT_REGULAR;
        }

        $sortTemp[] = &$resultTemp;

        array_multisort(...$sortTemp);

        $objectRows = [];

        foreach ($resultTemp as $row) {
            $objectRows[] = (object)$row;
        }

        return $objectRows;
    }


}