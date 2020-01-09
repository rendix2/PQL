<?php

namespace query\Join;

use Condition;

/**
 * Class HashJoin
 *
 * HashJoin is used for joins if Condition Operator is EQUAL ("=") which is used
 *
 * @package query\Join
 */
class HashJoin implements IJoin
{
    /**
     * @inheritDoc
     */
    public static function leftJoin(array $tableA, array $tableB, Condition $condition)
    {
        // hash phase
        $hashTable = [];

        $missingColumns = OuterJoinHelper::createNullColumns($tableB);

        foreach ($tableB as $rowB) {
            $hashTable[$rowB[$condition->getValue()]][] = $rowB;
        }

        // join phase
        $leftJoinResult = [];

        foreach ($tableA as $rowA) {
            if (isset($hashTable[$rowA[$condition->getColumn()]])) {
                foreach ($hashTable[$rowA[$condition->getColumn()]] as $hashRow) {
                    $leftJoinResult[] = array_merge($hashRow, $rowA);
                }
            } else {
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
        // hash phase
        $hashTable = [];

        $missingColumns = OuterJoinHelper::createNullColumns($tableA);

        foreach ($tableA as $rowA) {
            $hashTable[$rowA[$condition->getColumn()]][] = $rowA;
        }

        // join phase
        $rightJoinResult = [];

        foreach ($tableB as $rowB) {
            if (isset($hashTable[$rowB[$condition->getValue()]])) {
                foreach ($hashTable[$rowB[$condition->getValue()]] as $hashRow) {
                    $rightJoinResult[] = array_merge($hashRow, $rowB);
                }
            } else {
                $rightJoinResult[] = array_merge($rowB, $missingColumns);
            }
        }

        return $rightJoinResult;
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
        $left = self::leftJoin($tableA, $tableB, $condition);
        $right = self::rightJoin($tableA, $tableB, $condition);

        return OuterJoinHelper::removeDuplicities($left, $right);
    }

    /**
     * @inheritDoc
     */
    public static function innerJoin(array $tableA, array $tableB, Condition $condition)
    {
        // hash phase
        $hashTable = [];

        foreach ($tableB as $rowB) {
            $hashTable[$rowB[$condition->getValue()]][] = $rowB;
        }

        // join phase
        $innerJoinResult = [];

        foreach ($tableA as $rowA) {
            if (isset($hashTable[$rowA[$condition->getColumn()]])) {
                foreach ($hashTable[$rowA[$condition->getColumn()]] as $hashRow) {
                    $innerJoinResult[] = array_merge($hashRow, $rowA);
                }
            }
        }

        return $innerJoinResult;
    }
}
