<?php

namespace query\Join;

use Condition;
use query\ConditionHelper;

/**
 * Class NestedLoopJoin
 *
 * Simplest algorithm for joining two tables
 *
 * @package query\Join
 */
class NestedLoopJoin implements IJoin
{
    private static function createNullColumns(array $table)
    {
        $joinedColumnsTmp = array_keys($table[0]);
        $joinedColumns = [];

        foreach ($joinedColumnsTmp as $joinedColumn) {
            $joinedColumns[$joinedColumn] = null;
        }

        return $joinedColumns;
    }

    public static function leftJoin(array $tableA, array $tableB, Condition $condition)
    {
        $leftJoinResult = [];

        $rightNullJoinedColumns = self::createNullColumns($tableB);

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
                $leftJoinResult[] = array_merge($temporaryRow, $rightNullJoinedColumns);
            }
        }

        return $leftJoinResult;
    }

    public static function rightJoin(array $tableA, array $tableB, Condition $condition)
    {
        $rightJoinResult = [];

        $leftNullJoinedColumns = self::createNullColumns($tableA);

        foreach ($tableB as $temporaryRow) {
            $joined = false;

            foreach ($tableA as $joinedRow) {
                if (ConditionHelper::condition($condition, $temporaryRow, $joinedRow)) {
                    $rightJoinResult[] = array_merge($temporaryRow, $joinedRow);

                    $joined = true;
                    break;
                }
            }

            if (!$joined) {
                $rightJoinResult[] = array_merge($temporaryRow, $leftNullJoinedColumns);
            }
        }

        return $rightJoinResult;
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

    public static function fullJoin(array $tableA, array $tableB, Condition $condition)
    {
        $fullJoinResult = [];

        $leftNullJoinedColumns = self::createNullColumns($tableA);
        $rightNullJoinedColumns = self::createNullColumns($tableB);

        foreach ($tableA as $temporaryRow) {
            $joined = false;

            foreach ($tableB as $joinedRow) {
                if (ConditionHelper::condition($condition, $temporaryRow, $joinedRow)) {
                    $fullJoinResult[] = array_merge($temporaryRow, $joinedRow);

                    $joined = true;
                    break;
                }
            }

            if (!$joined) {
                $fullJoinResult[] = array_merge($temporaryRow, $rightNullJoinedColumns);
            }
        }

        foreach ($tableB as $temporaryRow) {
            $joined = false;

            foreach ($tableA as $joinedRow) {
                if (ConditionHelper::condition($condition, $temporaryRow, $joinedRow)) {
                    $fullJoinResult[] = array_merge($temporaryRow, $joinedRow);

                    $joined = true;
                    break;
                }
            }

            if (!$joined) {
                $fullJoinResult[] = array_merge($temporaryRow, $leftNullJoinedColumns);
            }
        }

        return array_unique($fullJoinResult);
    }
}