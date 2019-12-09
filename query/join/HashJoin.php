<?php

namespace query\Join;

use Condition;
use Exception;

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
        $h = [];

        $columnToLeftJoin = OuterJoinHelper::createNullColumns($tableB);

        unset($columnToLeftJoin[$condition->getValue()]);

        foreach ($tableB as $s) {
            $h[$s[$condition->getValue()]][] = $s;
        }

        // join phase
        $leftJoinResult = [];

        foreach ($tableA as $r) {
            if (isset($h[$r[$condition->getColumn()]])) {
                foreach ($h[$r[$condition->getColumn()]] as $s) {
                    $leftJoinResult[] = array_merge($s, $r);
                }
            } else {
                $leftJoinResult[] = array_merge($r, $columnToLeftJoin);
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
        throw new Exception('');
    }

    /**
     * @inheritDoc
     */
    public static function innerJoin(array $tableA, array $tableB, Condition $condition)
    {
        // hash phase
        $h = [];

        foreach ($tableB as $s) {
            $h[$s[$condition->getValue()]][] = $s;
        }

        // join phase
        $innerJoinResult = [];

        foreach ($tableA as $r) {
            if (isset($h[$r[$condition->getColumn()]])) {
                foreach ($h[$r[$condition->getColumn()]] as $s) {
                    $innerJoinResult[] = array_merge($s, $r);
                }
            }
        }

        return $innerJoinResult;
    }
}