<?php

namespace query\Join;

use Condition;

/**
 * Interface IJoin
 *
 * http://code.activestate.com/recipes/492216/
 *
 * @package query\Join
 */
interface IJoin
{
    /**
     * @param array     $tableA
     * @param array     $tableB
     * @param Condition $condition
     *
     * @return array
     */
    public static function leftJoin(array $tableA, array $tableB, Condition $condition);

    /**
     * @param array     $tableA
     * @param array     $tableB
     * @param Condition $condition
     *
     * @return array
     */
    public static function rightJoin(array $tableA, array $tableB, Condition $condition);

    /**
     * @param array     $tableA
     * @param array     $tableB
     * @param Condition $condition
     *
     * @return array
     */
    public static function innerJoin(array $tableA, array $tableB, Condition $condition);
}