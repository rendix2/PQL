<?php

namespace query\Join;

use Condition;
use query\ConditionHelper;

class NestedLoopJoin implements IJoin
{
    public static function leftJoin(array $tableA, array $tableB, Condition $condition)
    {
        $leftJoinResult = [];
        $joinedColumnsTmp = array_keys($tableB[0]);

        $joinedColumns = [];

        foreach ($joinedColumnsTmp as $joinedColumn) {
            $joinedColumns[$joinedColumn] = null;
        }

        foreach ($tableA as $temporaryRow) {
            $joined = false;

            foreach ($tableB as $joinedRow) {
                if (ConditionHelper::condition($condition, $temporaryRow, $joinedRow)) {
                    $leftJoinResult[] = array_merge($temporaryRow, $joinedRow);

                    $joined = true;
                    break;
                }
            }

            if (!$joined) {
                $leftJoinResult[] = array_merge($temporaryRow, $joinedColumns);
            }
        }

        return $leftJoinResult;
    }

    public static function rightJoin(array $tableA, array $tableB, Condition $condition)
    {
        throw new Exception('');
    }

    public static function innerJoin(array $tableA, array $tableB, Condition $condition)
    {
        $innerJoinResult = [];

        foreach ($tableA as $temporaryRow) {
            foreach ($tableB as $joinedRow) {
                if (ConditionHelper::condition($condition, $temporaryRow, $joinedRow)) {
                    $innerJoinResult[] = array_merge($temporaryRow, $joinedRow);
                }
            }
        }

        return $innerJoinResult;
    }

    public static function crossJoin(array $tableA, array $tableB)
    {
        $crossJoinResult = [];

        foreach ($tableA as $temporaryRow) {
            foreach ($tableB as $joinedRow) {
                $crossJoinResult[] = array_merge($temporaryRow, $joinedRow);
            }
        }

        return $crossJoinResult;
    }
}