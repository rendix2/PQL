<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 9. 12. 2019
 * Time: 16:36
 */

namespace query\Join;

/**
 * Class OuterJoinHelper
 *
 * @package query\Join
 */
class OuterJoinHelper
{
    /**
     * @param array $table
     *
     * @return array
     */
    public static function createNullColumns(array $table)
    {
        $joinedColumnsTmp = array_keys($table[0]);
        $joinedColumns = [];

        foreach ($joinedColumnsTmp as $joinedColumn) {
            $joinedColumns[$joinedColumn] = null;
        }

        return $joinedColumns;
    }
}
