<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 9. 12. 2019
 * Time: 16:36
 */

namespace pql\QueryExecute\Joins;

/**
 * Class OuterJoinHelper
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute\Joins
 */
class OuterJoinHelper
{
    /**
     * used in left and right joins
     *
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

    /**
     * used in fullJoin
     *
     * @param array $tableA
     * @param array $tableB
     *
     * @return array
     */
    public static function removeDuplicities(array $tableA, array $tableB)
    {
        $fullJoinResult = [];

        foreach ($tableA as $rowA) {
            foreach ($tableB as $rowB) {
                $merged = array_merge($rowA, $rowB);

                if (array_intersect($rowA, $rowB) === $merged) {
                    $fullJoinResult[] = $merged;
                    break;
                }
            }
        }

        return $fullJoinResult;
    }
}
