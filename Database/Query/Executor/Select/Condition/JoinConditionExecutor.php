<?php
/**
 *
 * Created by PhpStorm.
 * Filename: JoinConditionExecutor.php
 * User: Tomáš Babický
 * Date: 23.09.2021
 * Time: 10:29
 */

namespace PQL\Database\Query\Select\Condition;

use Nette\NotSupportedException;
use PQL\Database\Query\Builder\Expressions\JoinCondition;
use stdClass;

class JoinConditionExecutor
{

    public function run(stdClass $rowA, stdClass $rowB, JoinCondition $whereCondition) : bool
    {
        $left = $whereCondition->getLeft()->evaluate();
        $operator = $whereCondition->getOperator()->getOperator();
        $right = $whereCondition->getRight()?->evaluate();

        if ($operator === '=') {
            if (isset($rowA->{$left}) && isset($rowB->{$right})) {
                if ($rowA->{$left} === $rowB->{$right}) {
                    return true;
                }
            } elseif (isset($rowA->{$right}) && isset($rowB->{$left})) {
                if ($rowA->{$right} > $rowB->{$left}) {
                    return true;
                }
            }
        } else {
            throw new NotSupportedException('Unknown operator');
        }

        return false;
    }
}