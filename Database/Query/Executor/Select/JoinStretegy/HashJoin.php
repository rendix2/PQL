<?php
/**
 *
 * Created by PhpStorm.
 * Filename: HashJoin.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 12:22
 */

namespace PQL\Database\Query\Executor\Join;

use PQL\Database\Query\Builder\Expressions\ICondition;
use stdClass;

class HashJoin implements IJoin
{

    /**
     * SortMergeJoin constructor.
     *
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

    public function leftJoin(array $leftRows, array $rightRows, ICondition $where, stdClass $nullColumns) : array
    {
        // hash phase
        $hashTable = [];

        $leftPart = $where->getLeft()->evaluate();
        $rightPart = $where->getRight()->evaluate();

        foreach ($rightRows as $rightRow) {
            $hashTable[$rightRow->{$leftPart}][] = $rightRow;
        }

        // join phase
        $leftJoinResult = [];

        foreach ($leftRows as $leftRow) {
            if (isset($hashTable[$leftRow->{$rightPart}])) {
                foreach ($hashTable[$leftRow->{$rightPart}] as $hashRow) {
                    $leftJoinResult[] = (object)array_merge((array)$hashRow, (array)$leftRow);
                }
            } else {
                $leftJoinResult[] = (object)array_merge((array)$leftRow, (array)$nullColumns);
            }
        }

        return $leftJoinResult;
    }

    public function rightJoin(array $leftRows, array $rightRows, ICondition $where, array $nullColumns)
    {
        // TODO: Implement rightJoin() method.
    }

    public function innerJoin(array $leftRows, array $rightRows, ICondition $where)
    {
        // hash phase
        $hashTable = [];

        $leftPart = $where->getLeft()->evaluate();
        $rightPart = $where->getRight()->evaluate();

        foreach ($rightRows as $rightRow) {
            $hashTable[$rightRow->{$leftPart}][] = $rightRow;
        }

        // join phase
        $innerJoinResult = [];

        foreach ($leftRows as $leftRow) {
            if (isset($hashTable[$leftRow->{$rightPart}])) {
                foreach ($hashTable[$leftRow->{$rightPart}] as $hashRow) {
                    $innerJoinResult[] = (object)array_merge((array)$hashRow, (array)$leftRow);
                }
            }
        }

        return $innerJoinResult;
    }
}