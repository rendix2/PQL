<?php
/**
 *
 * Created by PhpStorm.
 * Filename: WhereConditionExecutor.php
 * User: Tomáš Babický
 * Date: 23.09.2021
 * Time: 10:28
 */

namespace PQL\Database\Query\Select\Condition;

use Exception;
use PQL\Database\Query\Builder\Expressions\WhereCondition;
use stdClass;

/**
 * Class WhereConditionExecutor
 *
 * @package PQL\Database\Query\Select\Condition
 */
class WhereConditionExecutor
{

    /**
     * @param stdClass       $row
     * @param WhereCondition $whereCondition
     *
     * @return bool
     * @throws Exception
     */
    public function run(stdClass $row, WhereCondition $whereCondition) : bool
    {
        $left = $whereCondition->getLeft()->evaluate();
        $operator = $whereCondition->getOperator()->getOperator();
        $right = $whereCondition->getRight()?->evaluate();

        $keyExists = array_key_exists($left, (array)$row);

        if ($operator === '=') {
            if (isset($keyExists) && $row->{$left} === $right) {
                return true;
            }
        } elseif ($operator === '>') {
            if ($keyExists && $row->{$left} > $right) {
                return true;
            }
        } elseif ($operator === '>=') {
            if ($keyExists && $row->{$left} >= $right) {
                return true;
            }
        } elseif ($operator === '<') {
            if ($keyExists && $row->{$left} < $right) {
                return true;
            }
        } elseif ($operator === '<=') {
            if ($keyExists && $row->{$left} <= $right) {
                return true;
            }
        } elseif ($operator === '!=' || $operator === '<>') {
            if ($keyExists && $row->{$left} !== $right) {
                return true;
            }
        } elseif ($operator === 'IN') {
            if ($keyExists && in_array($row->{$left}, $right, true)) {
                return true;
            }
        } elseif ($operator === 'NOT IN') {
            if ($keyExists && !in_array($row->{$left}, $right, true)) {
                return true;
            }
        } elseif ($operator === 'IS NULL') {
            if ($keyExists && is_null($row->{$left})) {
                return true;
            }
        } elseif ($operator === 'IS NOT NULL') {
            if ($keyExists && !is_null($row->{$left})) {
                return true;
            }
        } elseif ($operator === 'BETWEEN') {
            if ($keyExists && $row->{$left} > $right[0] && $row->{$left} < $right[1]) {
                return true;
            }
        } elseif ($operator === 'BETWEEN_INCLUSIVE') {
            if ($keyExists && $row->{$left} >= $right[0] && $row->{$left} <= $right[1]) {
                return true;
            }
        } else {
            throw new Exception('Unknown operator');
        }

        return false;
    }
}