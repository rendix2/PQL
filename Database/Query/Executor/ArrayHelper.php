<?php
/**
 *
 * Created by PhpStorm.
 * Filename: ArrayHelper.php
 * User: Tomáš Babický
 * Date: 22.10.2021
 * Time: 15:59
 */

namespace PQL\Query;

/**
 * Class ArrayHelper
 *
 * @package PQL\Query
 */
class ArrayHelper
{
    /**
     * @param array $rows
     *
     * @return array
     */
    public static function toArray(array $rows) : array
    {
        $resultRows = [];

        foreach ($rows as $row) {
            $resultRows[] = (array) $row;
        }

        return $resultRows;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    public static function toObject(array $rows) : array
    {
        $resultRows = [];

        foreach ($rows as $row) {
            $resultRows[] = (object) $row;
        }

        return $resultRows;
    }
}
