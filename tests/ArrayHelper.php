<?php
/**
 *
 * Created by PhpStorm.
 * Filename: ArrayHelper.php
 * User: Tomáš Babický
 * Date: 15.09.2021
 * Time: 23:38
 */

namespace PQL\Tests;

class ArrayHelper
{

    public static function createArray(array $stdRows) : array
    {
        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

}