<?php

namespace pql\QueryExecutor\Joins;

use pql\Condition;
use pql\QueryExecutor\ConditionHelper;

/**
 * Class NestedLoopJoin
 *
 * Simplest algorithm for joining two tables
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute\Joins
 */
class NestedLoopJoin implements IJoin
{
    public static function leftJoin(array $tableA, array $tableB, Condition $condition): array
    {
        $leftJoinResult = [];

        $missingColumns = OuterJoinHelper::createNullColumns($tableB);

        foreach ($tableA as $rowA) {
            $joined = false;

            foreach ($tableB as $rowB) {
                if (ConditionHelper::condition($condition, $rowA, $rowB)) {
                    $leftJoinResult[] = array_merge($rowA, $rowB);

                    $joined = true;
                }
            }

            if (!$joined) {
                $leftJoinResult[] = array_merge($rowA, $missingColumns);
            }
        }

        return $leftJoinResult;
    }

    public static function rightJoin(array $tableA, array $tableB, Condition $condition): array
    {
        return self::leftJoin($tableB, $tableA, $condition);
    }

    public static function innerJoin(array $tableA, array $tableB, Condition $condition): array
    {
        $innerJoinResult = [];

        foreach ($tableA as $rowA) {
            foreach ($tableB as $rowB) {
                if (ConditionHelper::condition($condition, $rowA, $rowB)) {
                    $innerJoinResult[] = array_merge($rowA, $rowB);
                }
            }
        }

        return $innerJoinResult;
    }

    public static function crossJoin(array $tableA, array $tableB): array
    {
        $crossJoinResult = [];

        foreach ($tableA as $rowA) {
            foreach ($tableB as $rowB) {
                $crossJoinResult[] = array_merge($rowA, $rowB);
            }
        }

        return $crossJoinResult;
    }

    public static function fullJoin(array $tableA, array $tableB, Condition $condition): array
    {
        $left  = self::leftJoin($tableA, $tableB, $condition);
        $right = self::rightJoin($tableA, $tableB, $condition);

        return OuterJoinHelper::removeDuplicities($left, $right);
    }
}
