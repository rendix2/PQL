<?php

namespace pql\query\Joins;

use pql\Condition;

/**
 * Interface IJoin
 *
 * @author  rendix2 <rendix2@seznam.cz>
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
