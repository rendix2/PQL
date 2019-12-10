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
    }

    /**
     * @inheritDoc
     */
    public static function rightJoin(array $tableA, array $tableB, Condition $condition)
    {
        throw new \Exception('Unsupprted');
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
