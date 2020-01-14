<?php

namespace pql\QueryExecute\Joins;

use pql\Condition;
use pql\ConditionHelper;

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
    /**
     * @inheritDoc
     */
    public static function leftJoin(array $tableA, array $tableB, Condition $condition)
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

        foreach ($tableA as $rowA) {
            foreach ($tableB as $rowB) {
                if (ConditionHelper::condition($condition, $rowA, $rowB)) {
                    $innerJoinResult[] = array_merge($rowA, $rowB);
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

        foreach ($tableA as $rowA) {
            foreach ($tableB as $rowB) {
                $crossJoinResult[] = array_merge($rowA, $rowB);
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
        $left  = self::leftJoin($tableA, $tableB, $condition);
        $right = self::rightJoin($tableA, $tableB, $condition);

        return OuterJoinHelper::removeDuplicities($left, $right);
    }
}
