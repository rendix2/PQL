<?php

namespace pql\QueryExecutor\Joins;

use pql\Condition;

/**
 * Interface IJoin
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute\Joins
 */
interface IJoin
{
    public static function leftJoin(array $tableA, array $tableB, Condition $condition): array;

    public static function rightJoin(array $tableA, array $tableB, Condition $condition): array;

    public static function innerJoin(array $tableA, array $tableB, Condition $condition): array;
}
