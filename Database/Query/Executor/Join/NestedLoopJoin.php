<?php
/**
 *
 * Created by PhpStorm.
 * Filename: NestedLoop.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 12:23
 */

namespace PQL\Query\Runner\Join;

use PQL\Query\Builder\Expressions\ICondition;
use PQL\Query\Runner\ConditionExecutor;
use stdClass;

class NestedLoopJoin implements IJoin
{
    private ConditionExecutor $conditionExecutor;

    /**
     * SortMergeJoin constructor.
     *
     * @param ConditionExecutor $conditionExecutor
     */
    public function __construct(ConditionExecutor $conditionExecutor)
    {
        $this->conditionExecutor = $conditionExecutor;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function leftJoin(array $leftRows, array $rightRows, ICondition $where, stdClass $nullColumns) : array
    {
        $resultRows = [];

        foreach ($leftRows as $leftRow) {
            $joined = false;

            foreach ($rightRows as $rightRow) {
                if ($this->conditionExecutor->join($leftRow, $rightRow, $where)) {
                    $resultRows[] = (object)array_merge((array)$leftRow, (array)$rightRow);

                    $joined = true;
                }
            }

            if (!$joined) {
                $resultRows[]  = (object)array_merge((array)$leftRow, (array)$nullColumns);
            }
        }

        return $resultRows;
    }

    public function rightJoin(array $aRows, array $bRows, ICondition $where, array $nullColumns) : array
    {
        // TODO: Implement rightJoin() method.
    }

    public function innerJoin(array $leftRows, array $rightRows, ICondition $where) : array
    {
        $resultRows = [];

        foreach ($leftRows as $leftRow) {
            foreach ($rightRows as $rightRow) {
                if ($this->conditionExecutor->join($leftRow, $rightRow, $where)) {
                    $resultRows[] = (object)array_merge((array)$leftRow, (array)$rightRow);
                }
            }
        }

        return $resultRows;
    }

    public function crossJoin(array $leftRows, array $rightRows) : array
    {
        $resultRows = [];

        foreach ($leftRows as $leftRow) {
            foreach ($rightRows as $rightRow) {
                $resultRows[] = (object)array_merge((array)$leftRow, (array)$rightRow);
            }
        }

        return $resultRows;
    }
}