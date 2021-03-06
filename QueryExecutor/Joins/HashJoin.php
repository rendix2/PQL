<?php

namespace pql\QueryExecutor\Joins;

use pql\Condition;

/**
 * Class HashJoin
 *
 * HashJoin is used for joins if Condition Operator is EQUAL ("=") which is used
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute\Joins
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
            $hashTable[$rowB[$condition->getValue()->evaluate()]][] = $rowB;
        }

        // join phase
        $leftJoinResult = [];

        foreach ($tableA as $rowA) {
            if (isset($hashTable[$rowA[$condition->getColumn()->evaluate()]])) {
                foreach ($hashTable[$rowA[$condition->getColumn()->evaluate()]] as $hashRow) {
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
            $hashTable[$rowA[$condition->getColumn()->evaluate()]][] = $rowA;
        }

        // join phase
        $rightJoinResult = [];

        foreach ($tableB as $rowB) {
            if (isset($hashTable[$rowB[$condition->getValue()->evaluate()]])) {
                foreach ($hashTable[$rowB[$condition->getValue()->evaluate()]] as $hashRow) {
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
            $hashTable[$rowB[$condition->getValue()->evaluate()]][] = $rowB;
        }

        // join phase
        $innerJoinResult = [];

        foreach ($tableA as $rowA) {
            if (isset($hashTable[$rowA[$condition->getColumn()->evaluate()]])) {
                foreach ($hashTable[$rowA[$condition->getColumn()->evaluate()]] as $hashRow) {
                    $innerJoinResult[] = array_merge($hashRow, $rowA);
                }
            }
        }

        return $innerJoinResult;
    }
}
