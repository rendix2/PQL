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
        throw new \Exception('Unsupprted');

        $indexA = 0;
        $indexB = 0;

        $lastA = count($tableA);
        $lastB = count($tableB);

        $columnAData = array_column($tableA, $condition->getColumn());
        $columnBData = array_column($tableB, $condition->getValue());

        array_multisort($columnAData, SORT_ASC, $tableA);
        array_multisort($columnBData, SORT_ASC, $tableB);

        $missingColumns = OuterJoinHelper::createNullColumns($tableB);

        $res = $tableA;

        do {
            $pa = $tableA[$indexA];
            $pb = $tableB[$indexB];

            if (ConditionHelper::condition($condition, $pa, $pb)) {
                $res[$indexA] = array_merge($res[$indexA], $pb);
            } else {
                $res[$indexA] = array_merge($res[$indexA], $missingColumns);
            }

            if ($pa[$condition->getColumn()] <= $pb[$condition->getValue()]) {
                $indexA++;
            } else {
                $indexB++;
            }
        } while($indexA !== $lastA && $indexB !== $lastB);

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
        $indexA = 0;
        $indexB = 0;

        $lastA = count($tableA);
        $lastB = count($tableB);

        $res = [];

        $columnAData = array_column($tableA, $condition->getColumn());
        $columnBData = array_column($tableB, $condition->getValue());

        array_multisort($columnAData, SORT_ASC, $tableA);
        array_multisort($columnBData, SORT_ASC, $tableB);

        do {
            $pa = $tableA[$indexA];
            $pb = $tableB[$indexB];

            if (ConditionHelper::condition($condition, $pa, $pb)) {
               $res[] = array_merge($pa, $pb);
            }

            if ($pa[$condition->getColumn()] <= $pb[$condition->getValue()]) {
                $indexA++;
            } else {
                $indexB++;
            }
        } while($indexA !== $lastA && $indexB !== $lastB);

        return $res;
    }
}
