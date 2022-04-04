<?php
/**
 *
 * Created by PhpStorm.
 * Filename: BlockNestedLoopJoin.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 12:36
 */

namespace PQL\Database\Query\Executor\Join;


use PQL\Database\Query\Builder\Expressions\ICondition;
use stdClass;

class BlockNestedLoopJoin implements IJoin
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
        // TODO: Implement innerJoin() method.
    }
}