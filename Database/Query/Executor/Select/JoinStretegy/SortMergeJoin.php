<?php
/**
 *
 * Created by PhpStorm.
 * Filename: SortMergeJoin.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 12:26
 */

namespace PQL\Database\Query\Executor\Join;

use PQL\Database\Query\Builder\Expressions\ICondition;
use stdClass;

/**
 * Class SortMergeJoin
 *
 * @package PQL\Database\Query\Executor\Join
 */
class SortMergeJoin implements IJoin
{
    /**
     * SortMergeJoin constructor.
     */
    public function __construct()
    {
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @param array      $leftRows
     * @param array      $rightRows
     * @param ICondition $where
     * @param stdClass   $nullColumns
     */
    public function leftJoin(array $leftRows, array $rightRows, ICondition $where, stdClass $nullColumns)
    {
        // TODO: Implement leftJoin() method.
    }

    public function rightJoin(array $leftRows, array $rightRows, ICondition $where, array $nullColumns)
    {
        // TODO: Implement rightJoin() method.
    }

    public function innerJoin(array $leftRows, array $rightRows, ICondition $where)
    {
        $left = $where->getRight()->evaluate();
        $right = $where->getLeft()->evaluate();

        $i = 0;
        $j = 0;

        $leftCount = count($leftRows) - 1;
        $rightCount = count($rightRows) - 1;

        $resultRows = [];

        $columnAData = array_column($leftRows, $left);
        $columnBData = array_column($rightRows, $right);

        array_multisort($columnAData, SORT_ASC, $leftRows);
        array_multisort($columnBData, SORT_ASC, $rightRows);

        while ($i <= $leftCount && $j <= $rightCount) {
            $leftRow = $leftRows[$i];
            $rightRow = $rightRows[$j];

            if ($leftRow->{$left} > $rightRow->{$right}) {
                $j++;
            } elseif ($leftRow->{$left} < $rightRow->{$right}) {
                $i++;
            } else {
                $resultRows[] = (object)array_merge((array)$leftRow, (array)$rightRow);

                $i1 = $i + 1;

                while ($i1 <= $leftCount && $leftRows[$i1]->{$left} === $rightRow->{$right}) {
                    $resultRows[] = (object)array_merge((array)$leftRows[$i1], (array)$rightRow);
                    $i1++;
                }

                $j1 = $j + 1;

                while ($j1 <= $rightCount && $leftRow->{$left} === $rightRows[$j1]->{$right}) {
                    $resultRows[] = (object)array_merge((array)$leftRow, (array)$rightRows[$j1]);
                    $j1++;
                }

                $i++;
                $j++;
            }
        }

        return $resultRows;
    }
}