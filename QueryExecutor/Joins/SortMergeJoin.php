<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 12. 2019
 * Time: 13:26
 */

namespace pql\QueryExecutor\Joins;

use pql\Condition;

class SortMergeJoin implements IJoin
{
    public static function leftJoin(array $tableA, array $tableB, Condition $condition): array
    {
        $result = [];

        $columnAData = array_column($tableA, $condition->getColumn()->evaluate());
        $columnBData = array_column($tableB, $condition->getValue()->evaluate());

        array_multisort($columnAData, SORT_ASC, $tableA);
        array_multisort($columnBData, SORT_ASC, $tableB);

        $missingColumns = OuterJoinHelper::createNullColumns($tableB);

        $r = 0;
        $q = 0;

        $lastR = count($tableA) - 1;
        $lastQ = count($tableB) - 1;

        $temp = [];

        while ($r <= $lastR && $q <= $lastQ) {
            $rRow = $tableA[$r];
            $qRow = $tableB[$q];

            if ($rRow[$condition->getColumn()->evaluate()] > $qRow[$condition->getValue()->evaluate()]) {
                $temp[$rRow[$condition->getColumn()->evaluate()]] = $rRow;
                $q++;
            } elseif ($rRow[$condition->getColumn()->evaluate()] < $qRow[$condition->getValue()->evaluate()]) {
                $r++;
            } else {
                $result[] = array_merge($rRow, $qRow);
                unset($temp[$rRow[$condition->getColumn()->evaluate()]]);

                $matched = false;

                $q1 = $q+1;

                while ($q1 <= $lastQ && $rRow[$condition->getColumn()->evaluate()] === $tableB[$q1][$condition->getValue()->evaluate()]) {
                    $result[] = array_merge($rRow, $tableB[$q1]);
                    $q1++;
                    $matched = true;
                }

                $matched2 = false;

                $r1 = $r+1;

                while ($r1 <= $lastR && $tableA[$r1][$condition->getColumn()->evaluate()] === $qRow[$condition->getValue()->evaluate()]) {
                    $result[] = array_merge($tableA[$r1], $qRow);
                    $r1++;
                    $matched2 = true;
                }

                if (!$matched && !$matched2) {
                    $q--;
                }

                $r++;
                $q++;
            }
        }

        foreach ($temp as $tempRow) {
            $result[] = array_merge($tempRow, $missingColumns);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public static function rightJoin(array $tableA, array $tableB, Condition $condition): array
    {
        $result = [];

        $columnAData = array_column($tableA, $condition->getColumn()->evaluate());
        $columnBData = array_column($tableB, $condition->getValue()->evaluate());

        array_multisort($columnAData, SORT_ASC, $tableA);
        array_multisort($columnBData, SORT_ASC, $tableB);

        $missingColumns = OuterJoinHelper::createNullColumns($tableA);

        $r = 0;
        $q = 0;

        $lastR = count($tableA) - 1;
        $lastQ = count($tableB) - 1;

        $temp = [];

        while ($r <= $lastR && $q <= $lastQ) {
            $rRow = $tableA[$r];
            $qRow = $tableB[$q];

            if ($rRow[$condition->getColumn()->evaluate()] > $qRow[$condition->getValue()->evaluate()]) {
                $q++;
            } elseif ($rRow[$condition->getColumn()->evaluate()] < $qRow[$condition->getValue()->evaluate()]) {
                $temp[$qRow[$condition->getValue()->evaluate()]] = $qRow;
                $r++;
            } else {
                $result[] = array_merge($rRow, $qRow);
                unset($temp[$rRow[$condition->getColumn()->evaluate()]]);

                $matched = false;

                $q1 = $q+1;

                while ($q1 <= $lastQ && $rRow[$condition->getColumn()->evaluate()] === $tableB[$q1][$condition->getValue()->evaluate()]) {
                    $result[] = array_merge($rRow, $tableB[$q1]);
                    $q1++;
                    $matched = true;
                }

                $matched2 = false;

                $r1 = $r+1;

                while ($r1 <= $lastR && $tableA[$r1][$condition->getColumn()->evaluate()] === $qRow[$condition->getValue()->evaluate()]) {
                    $result[] = array_merge($tableA[$r1], $qRow);
                    $r1++;
                    $matched2 = true;
                }

                if (!$matched && !$matched2) {
                    $q--;
                }

                $r++;
                $q++;
            }
        }

        foreach ($temp as $tempRow) {
            $result[] = array_merge($tempRow, $missingColumns);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public static function innerJoin(array $tableA, array $tableB, Condition $condition): array
    {
        $res = [];

        $columnAData = array_column($tableA, $condition->getColumn()->evaluate());
        $columnBData = array_column($tableB, $condition->getValue()->evaluate());

        array_multisort($columnAData, SORT_ASC, $tableA);
        array_multisort($columnBData, SORT_ASC, $tableB);

        $r = 0;
        $q = 0;

        $lastR = count($tableA) - 1;
        $lastQ = count($tableB) - 1;

        while ($r <= $lastR && $q <= $lastQ) {
            $rRow = $tableA[$r];
            $qRow = $tableB[$q];

            if ($rRow[$condition->getColumn()->evaluate()] > $qRow[$condition->getValue()->evaluate()]) {
                $q++;
            } elseif ($rRow[$condition->getColumn()->evaluate()] < $qRow[$condition->getValue()->evaluate()]) {
                $r++;
            } else {
                $res[] = array_merge($rRow, $qRow);

                $q1 = $q+1;

                while ($q1 <= $lastQ && $rRow[$condition->getColumn()->evaluate()] === $tableB[$q1][$condition->getValue()->evaluate()]) {
                    $res[] = array_merge($rRow, $tableB[$q1]);
                    $q1++;
                }

                $r1 = $r+1;

                while ($r1 <= $lastR && $tableA[$r1][$condition->getColumn()->evaluate()] === $qRow[$condition->getValue()->evaluate()]) {
                    $res[] = array_merge($tableA[$r1], $qRow);
                    $r1++;
                }

                $r++;
                $q++;
            }
        }

        return $res;
    }

    public static function fullJoin(array $tableA, array $tableB, Condition $condition): array
    {
        $left = self::leftJoin($tableA, $tableB, $condition);
        $right = self::rightJoin($tableA, $tableB, $condition);

        return OuterJoinHelper::removeDuplicities($left, $right);
    }
}
