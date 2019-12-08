<?php

namespace query\Join;

use Condition;

interface IJoin
{
    public static function leftJoin(array $tableA, array $tableB, Condition $condition);

    public static function rightJoin(array $tableA, array $tableB, Condition $condition);

    public static function innerJoin(array $tableA, array $tableB, Condition $condition);
}