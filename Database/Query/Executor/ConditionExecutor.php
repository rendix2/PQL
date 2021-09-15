<?php
/**
 *
 * Created by PhpStorm.
 * Filename: WhereCondition.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 11:26
 */

namespace PQL\Query\Runner;


use Exception;
use PQL\Query\Builder\Expressions\Column;
use PQL\Query\Builder\Expressions\HavingCondition;
use PQL\Query\Builder\Expressions\IExpression;
use PQL\Query\Builder\Expressions\IValue;
use PQL\Query\Builder\Expressions\JoinCondition;
use PQL\Query\Builder\Expressions\WhereCondition;
use stdClass;

class ConditionExecutor
{
    /**
     * @param stdClass       $row
     * @param WhereCondition $whereCondition
     *
     * @return bool
     * @throws Exception
     */
    public function where(stdClass $row, WhereCondition $whereCondition) : bool
    {
        $left = $whereCondition->getLeft()->evaluate();
        $operator = $whereCondition->getOperator()->getOperator();
        $right = $whereCondition->getRight()?->evaluate();

        if ($operator === '=') {
            if ($row->{$left} === $right) {
                return true;
            }
        } elseif($operator === '>') {
            if ($row->{$left} > $right) {
                return true;
            }
        } elseif($operator === '>=') {
            if ($row->{$left} >= $right) {
                return true;
            }
        } elseif($operator === '<') {
            if ($row->{$left} < $right) {
                return true;
            }
        } elseif($operator === '<=') {
            if ($row->{$left} <= $right) {
                return true;
            }
        } elseif ($operator === '!=' || $operator === '<>') {
            if ($row->{$left} !== $right) {
                return true;
            }
        } elseif ($operator === 'IN') {
            if (in_array($row->{$left}, $right, true)) {
                return true;
            }
        } elseif ($operator === 'NOT IN') {
            if (!in_array($row->{$left}, $right, true)) {
                return true;
            }
        } elseif ($operator === 'IS NULL') {
            if (is_null($row->{$left})) {
                return true;
            }
        } elseif ($operator === 'IS NOT NULL') {
            if (!is_null($row->{$left})) {
                return true;
            }
        } elseif ($operator === 'BETWEEN') {
            if ($row->{$left} > $right[0] && $row->{$left} < $right[1]) {
                return true;
            }
        }  elseif ($operator === 'BETWEEN_INCLUSIVE') {
            if ($row->{$left} >= $right[0] && $row->{$left} <= $right[1]) {
                return true;
            }
        } else {
            throw new Exception('Unknown operator');
        }

        return false;
    }

    public function join(stdClass $rowA, stdClass $rowB, JoinCondition $whereCondition) : bool
    {
        if ($whereCondition->getLeft() instanceof Column) {
            $leftColumn = $whereCondition->getLeft()->evaluate();
        } else {
            $leftColumn = null;
        }

        if ($whereCondition->getRight() instanceof Column) {
            $rightColumn = $whereCondition->getRight()->evaluate();
        } else {
            $rightColumn = null;
        }


        $operator = $whereCondition->getOperator()->evaluate();

        $leftOperatorColumn = $whereCondition->getLeft() instanceof Column;
        $rightOperatorColumn = $whereCondition->getRight() instanceof Column;

        $leftOperatorValue = $whereCondition->getLeft() instanceof IValue;
        $rightOperatorValue = $whereCondition->getRight() instanceof IValue;

        $rowALeft = isset($rowA->{$leftColumn}) && isset($rowB->{$rightColumn});
        $rowARight = isset($rowA->{$rightColumn}) && isset($rowB->{$leftColumn});

        if ($operator === '=') {
           if (isset($rowA->{$rightColumn}) && isset($rowB->{$leftColumn}) && $rowA->{$rightColumn} === $rowB->{$leftColumn}) {
               return true;
           }

           if ($rightOperatorValue) {
               if (isset($rowA->{$leftColumn})) {
                   if ($rowA->{$leftColumn} === $whereCondition->getRight()->evaluate()) {
                       return true;
                   }
               }
           }
        }

        return false;
    }

    /**
     * @param float           $countedValue
     * @param HavingCondition $havingCondition
     *
     * @return bool
     * @throws Exception
     */
    public function having(float $countedValue, HavingCondition $havingCondition) : bool
    {
        $conditionValue = (float)$havingCondition->getRight()->evaluate();
        $operator = $havingCondition->getOperator()->getOperator();

        if ($operator === '=') {
            if ($countedValue === $conditionValue) {
                return true;
            }
        } elseif ($operator === '>') {
            if ($countedValue > $conditionValue) {
                return true;
            }
        } elseif ($operator === '>=') {
            if ($countedValue >= $conditionValue) {
                return true;
            }
        } elseif ($operator === '<') {
            if ($countedValue < $conditionValue) {
                return true;
            }
        } elseif ($operator === '<=') {
            if ($countedValue <= $conditionValue) {
                return true;
            }
        } elseif ($operator === '!=' || $operator === '<>') {
            if ($countedValue !== $conditionValue) {
                return true;
            }
        } else {
            throw new Exception('Unknown operator');
        }

        return false;
    }
}