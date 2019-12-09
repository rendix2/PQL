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
                    break;
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
     * @throws Exception
     */
    public static function rightJoin(array $tableA, array $tableB, Condition $condition)
    {
        throw new Exception('Unsupported operation.');
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
     * @param array     $tableA
     * @param array     $tableB
     * @param Condition $condition
     *
     * @return array
     */
    public static function fullJoin(array $tableA, array $tableB, Condition $condition)
    {
        $fullJoinResult = [];

        $leftNullJoinedColumns = OuterJoinHelper::createNullColumns($tableA);
        $rightNullJoinedColumns = OuterJoinHelper::createNullColumns($tableB);

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