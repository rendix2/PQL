<?php

namespace query\Join;

use Condition;
use Exception;
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
    /**
     * @inheritDoc
     */
    public static function leftJoin(array $tableA, array $tableB, Condition $condition)
    {
        $leftJoinResult = [];

        $rightNullJoinedColumns = OuterJoinHelper::createNullColumns($tableB);

        foreach ($tableA as $temporaryRow) {
            $joined = false;

            foreach ($tableB as $joinedRow) {
                if (ConditionHelper::condition($condition, $temporaryRow, $joinedRow)) {
                    $leftJoinResult[] = array_merge($temporaryRow, $joinedRow);

                    $joined = true;
                }
            }

            if (!$joined) {
                $leftJoinResult[] = array_merge($temporaryRow, $rightNullJoinedColumns);
            }
        }

        return $leftJoinResult;
    }

    /**
     * @inheritDoc
     */
    public static function rightJoin(array $tableA, array $tableB, Condition $condition)
    {
        return self::leftJoin($tableB, $tableA, $condition);
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @param array $tableA
     * @param array $tableB
     *
     * @return array
     */
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

    /**
     * @param array $tableA
     * @param array $tableB
     * @param Condition $condition
     *
     * @return array
     */
    public static function fullJoin(array $tableA, array $tableB, Condition $condition)
    {
        $leftNullJoinedColumns = OuterJoinHelper::createNullColumns($tableA);
        $rightNullJoinedColumns = OuterJoinHelper::createNullColumns($tableB);

        $left  = [];
        $right = [];

        foreach ($tableA as $temporaryRow) {
            $joined = false;

            foreach ($tableB as $joinedRow) {
                if (ConditionHelper::condition($condition, $temporaryRow, $joinedRow)) {
                    $left[] = array_merge($temporaryRow, $joinedRow);

                    $joined = true;
                }
            }

            if (!$joined) {
                $left[] = array_merge($temporaryRow, $rightNullJoinedColumns);
            }
        }

        foreach ($tableB as $temporaryRow) {
            $joined = false;

            foreach ($tableA as $joinedRow) {
                if (ConditionHelper::condition($condition, $temporaryRow, $joinedRow)) {
                    $right[] = array_merge($temporaryRow, $joinedRow);

                    $joined = true;
                }
            }

            if (!$joined) {
                $right[] = array_merge($temporaryRow, $leftNullJoinedColumns);
            }
        }

        $fullJoinResult = [];

        foreach ($left as $rowL) {
            foreach ($right as $rowR) {
                $merged = array_merge($rowL, $rowR);

                if (array_intersect($rowL, $rowR) === $merged) {
                    $fullJoinResult[] = $merged;
                    break;
                }
            }
        }

        return $fullJoinResult;
    }
}