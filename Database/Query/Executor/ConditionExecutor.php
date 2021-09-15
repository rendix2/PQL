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
     */
    public function where(stdClass $row, WhereCondition $whereCondition) : bool
    {
        if ($whereCondition->getLeft() instanceof IExpression) {
            $left = $whereCondition->getLeft()->evaluate();
        } else {
            throw new Exception('');
        }

        if ($whereCondition->getRight() instanceof IExpression) {
            $right = $whereCondition->getRight()->evaluate();
        } elseif($whereCondition->getRight() === null) {
            $right = null;
        } else {
            throw new Exception('');
        }

        $operator = $whereCondition->getOperator()->getOperator();

        if ($operator === '=' && isset($row->{$left}) && $row->{$left} === $right) {
            return true;
        }

        if ($operator === 'IS NOT NULL' && $row->{$left} !== null) {
            return true;
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