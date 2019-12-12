<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 12. 2019
 * Time: 13:26
 */

namespace query\Join;

use Condition;
use query\ConditionHelper;

/**
 * Class SortMergeJoin
 *
 * @package query\Join
 * @author  Tomáš Babický tomas.babicky@websta.de
 */
class SortMergeJoin implements IJoin
{
    /**
     * @inheritDoc
     */
    public static function leftJoin(array $tableA, array $tableB, Condition $condition)
    {
        $res = [];

        $columnAData = array_column($tableA, $condition->getColumn());
        $columnBData = array_column($tableB, $condition->getValue());

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

            if ($rRow[$condition->getColumn()] > $qRow[$condition->getValue()]) {
                $temp[$rRow[$condition->getColumn()]] = $rRow;
                $q++;
            } elseif ($rRow[$condition->getColumn()] < $qRow[$condition->getValue()]) {

                $r++;
            } else {
                $res[] = array_merge($rRow, $qRow);
                unset($temp[$rRow[$condition->getColumn()]]);;

                $matched = false;

                $q1 = $q+1;

                while ($q1 <= $lastQ && $rRow[$condition->getColumn()] === $tableB[$q1][$condition->getValue()]) {
                    $res[] = array_merge($rRow, $tableB[$q1]);
                    $q1++;
                    $matched = true;
                }

                $matched2 = false;

                $r1 = $r+1;

                while ($r1 <= $lastR && $tableA[$r1][$condition->getColumn()] === $qRow[$condition->getValue()]) {
                    $res[] = array_merge($tableA[$r1], $qRow);
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

        foreach ($temp as $tempROw) {
            $res[] = array_merge($tempROw, $missingColumns);
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    public static function rightJoin(array $tableA, array $tableB, Condition $condition)
    {
        throw new \Exception('Unsupprted');

        $indexA = 0;
        $indexB = 0;

        $lastA = count($tableA)-1;
        $lastB = count($tableB)-1;

        bdump($lastA, '$lastA');
        bdump($lastB, '$lastB');

        $columnAData = array_column($tableA, $condition->getColumn());
        $columnBData = array_column($tableB, $condition->getValue());

        array_multisort($columnAData, SORT_ASC, $tableA);
        array_multisort($columnBData, SORT_ASC, $tableB);

        $missingColumns = OuterJoinHelper::createNullColumns($tableA);

        $res = [];

        foreach ($res as $i => $row) {
             //$res[$i] = array_merge($row, $missingColumns);
        }

        do {
            $pa = $tableA[$indexA];
            $pb = $tableB[$indexB];

            $joined = false;

            //bdump($indexA, '$indexA');
            //bdump($indexB, '$indexB');

            if ($pa['order_id'] === $pb['user_order_id']) {
                $res[] = array_merge($pb, $pa);
                //$indexA++;
                //$indexB++;

                $joined = true;
            }

            if ($pa['order_id'] <= $pb['user_order_id']) {
                $indexA++;
            } else {
                $indexB++;
            }
        } while($lastA >= $indexA && $lastB >= $indexB);

        foreach ($res as $i => $row) {
            //$res[$i] = array_merge($row, $missingColumns);
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    public static function innerJoin(array $tableA, array $tableB, Condition $condition)
    {
        $res = [];

        $columnAData = array_column($tableA, $condition->getColumn());
        $columnBData = array_column($tableB, $condition->getValue());

        array_multisort($columnAData, SORT_ASC, $tableA);
        array_multisort($columnBData, SORT_ASC, $tableB);

        $r = 0;
        $q = 0;

        $lastR = count($tableA) - 1;
        $lastQ = count($tableB) - 1;

        while ($r <= $lastR && $q <= $lastQ) {
            $rRow = $tableA[$r];
            $qRow = $tableB[$q];

            if ($rRow[$condition->getColumn()] > $qRow[$condition->getValue()]) {
                $q++;
            } elseif ($rRow[$condition->getColumn()] < $qRow[$condition->getValue()]) {

                $r++;
            } else {
                $res[] = array_merge($rRow, $qRow);

                $q1 = $q+1;

                while ($q1 <= $lastQ && $rRow[$condition->getColumn()] === $tableB[$q1][$condition->getValue()]) {
                    $res[] = array_merge($rRow, $tableB[$q1]);
                    $q1++;
                }

                $r1 = $r+1;

                while ($r1 <= $lastR && $tableA[$r1][$condition->getColumn()] === $qRow[$condition->getValue()]) {
                    $res[] = array_merge($tableA[$r1], $qRow);
                    $r1++;
                }

                $r++;
                $q++;
            }
        }

        return $res;
    }
}
