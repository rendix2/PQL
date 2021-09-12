<?php
/**
 *
 * Created by PhpStorm.
 * Filename: BlockNestedLoopJoin.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 12:36
 */

namespace PQL\Query\Runner\Join;


use PQL\Query\Builder\Expressions\ICondition;
use PQL\Query\Runner\ConditionExecutor;

class BlockNestedLoopJoin implements IJoin
{
    private ConditionExecutor $conditionExecutor;

    /**
     * SortMergeJoin constructor.
     *
     * @param ConditionExecutor $conditionExecutor
     */
    public function __construct(ConditionExecutor $conditionExecutor)
    {
        $this->conditionExecutor = $conditionExecutor;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function leftJoin(array $leftRows, array $rightRows, ICondition $where, \stdClass $nullColumns)
    {
        // TODO: Implement leftJoin() method.
    }

    public function rightJoin(array $aRows, array $bRows, ICondition $where)
    {
        // TODO: Implement rightJoin() method.
    }

    public function innerJoin(array $leftRows, array $rightRows, ICondition $where)
    {
        // TODO: Implement innerJoin() method.
    }
}