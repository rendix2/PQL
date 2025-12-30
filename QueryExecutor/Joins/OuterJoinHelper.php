<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 9. 12. 2019
 * Time: 16:36
 */

namespace pql\QueryExecutor\Joins;

class OuterJoinHelper
{
    public static function createNullColumns(array $table): array
    {
        $joinedColumnsTmp = array_keys($table[0]);
        $joinedColumns = [];

        foreach ($joinedColumnsTmp as $joinedColumn) {
            $joinedColumns[$joinedColumn] = null;
        }

        return $joinedColumns;
    }

    public static function removeDuplicities(array $tableA, array $tableB): array
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
