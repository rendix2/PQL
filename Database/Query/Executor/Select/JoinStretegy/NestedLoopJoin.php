<?php
/**
 *
 * Created by PhpStorm.
 * Filename: NestedLoop.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 12:23
 */

namespace PQL\Database\Query\Executor\Join;

use Nette\NotImplementedException;
use PQL\Database\Query\Builder\Expressions\ICondition;
use PQL\Database\Query\Select\Condition\JoinConditionExecutor;
use stdClass;

/**
 * Class NestedLoopJoin
 *
 * @package PQL\Database\Query\Executor\Join
 */
class NestedLoopJoin implements IJoin
{
    /**
     * @var JoinConditionExecutor $joinConditionExecutor
     */
    private JoinConditionExecutor $joinConditionExecutor;

    /**
     * SortMergeJoin constructor.
     *
     * @param JoinConditionExecutor $joinConditionExecutor
     */
    public function __construct(JoinConditionExecutor $joinConditionExecutor)
    {
        $this->joinConditionExecutor = $joinConditionExecutor;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function leftJoin(array $leftRows, array $rightRows, ICondition $where, stdClass $nullColumns) : array
    {
        $resultRows = [];

        foreach ($leftRows as $leftRow) {
            $joined = false;

            foreach ($rightRows as $rightRow) {
                if ($this->joinConditionExecutor->run($leftRow, $rightRow, $where)) {
                    $resultRows[] = (object)array_merge((array)$leftRow, (array)$rightRow);

                    $joined = true;
                }
            }

            if (!$joined) {
                $resultRows[]  = (object)array_merge((array)$leftRow, (array)$nullColumns);
            }
        }

        return $resultRows;
    }

    public function rightJoin(array $leftRows, array $rightRows, ICondition $where, array $nullColumns) : array
    {
        throw new NotImplementedException();
    }

    public function innerJoin(array $leftRows, array $rightRows, ICondition $where) : array
    {
        $resultRows = [];

        foreach ($leftRows as $leftRow) {
            foreach ($rightRows as $rightRow) {
                if ($this->joinConditionExecutor->run($leftRow, $rightRow, $where)) {
                    $resultRows[] = (object)array_merge((array)$leftRow, (array)$rightRow);
                }
            }
        }

        return $resultRows;
    }

    public function crossJoin(array $leftRows, array $rightRows) : array
    {
        $resultRows = [];

        foreach ($leftRows as $leftRow) {
            foreach ($rightRows as $rightRow) {
                $resultRows[] = (object)array_merge((array)$leftRow, (array)$rightRow);
            }
        }

        return $resultRows;
    }
}