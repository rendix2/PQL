<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Optimizer.php
 * User: Tomáš Babický
 * Date: 31.08.2021
 * Time: 1:12
 */

namespace PQL\Query\Runner;


use Nette\NotImplementedException;
use PQL\Query\Builder\Expressions\ICondition;
use PQL\Query\Runner\Join\HashJoin;
use PQL\Query\Runner\Join\IJoin;
use PQL\Query\Runner\Join\NestedLoopJoin;
use PQL\Query\Runner\Join\SortMergeJoin;
use stdClass;

class Optimizer implements IJoin
{

    private HashJoin $hashJoin;

    private NestedLoopJoin $nestedLoopJoin;

    private SortMergeJoin $sortMergeJoin;

    public function __construct(ConditionExecutor $conditionExecutor)
    {
        $this->hashJoin = new HashJoin($conditionExecutor);
        $this->nestedLoopJoin = new NestedLoopJoin($conditionExecutor);
        $this->sortMergeJoin = new SortMergeJoin($conditionExecutor);
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function leftJoin(array $leftRows, array $rightRows, ICondition $where, stdClass $nullColumns) : array
    {
        if ($where->getOperator()->getOperator() === '=') {
            return $this->hashJoin->leftJoin($leftRows, $rightRows, $where, $nullColumns);
        } else {
            return $this->nestedLoopJoin->leftJoin($leftRows, $rightRows, $where, $nullColumns);
        }
    }

    public function rightJoin(array $aRows, array $bRows, ICondition $where, array $nullColumns) : array
    {
        throw new NotImplementedException();
    }

    public function innerJoin(array $leftRows, array $rightRows, ICondition $where) : array
    {
        if ($where->getOperator()->getOperator() === '=') {
            return $this->hashJoin->innerJoin($leftRows, $rightRows, $where);
        } else {
            return $this->nestedLoopJoin->innerJoin($leftRows, $rightRows, $where);
        }
    }

    public function crossJoin(array $aRows, array $bRows,) : array
    {
       return $this->nestedLoopJoin->crossJoin($aRows, $bRows);
    }
}