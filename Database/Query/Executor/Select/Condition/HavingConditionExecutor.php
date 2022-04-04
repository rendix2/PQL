<?php
/**
 *
 * Created by PhpStorm.
 * Filename: HavingConditionExecutor.php
 * User: Tomáš Babický
 * Date: 23.09.2021
 * Time: 10:28
 */

namespace PQL\Database\Query\Select\Condition;

use Exception;
use PQL\Database\Query\Builder\Expressions\HavingCondition;

/**
 * Class HavingConditionExecutor
 *
 * @package PQL\Database\Query\Select\Condition
 */
class HavingConditionExecutor
{
    /**
     * @throws Exception
     */
    public function run(float $countedValue, HavingCondition $havingCondition) : bool
    {
        $conditionValue = $havingCondition->getRight()->evaluate();
        $floatConditionValue = (float)$conditionValue;
        $operator = $havingCondition->getOperator()->getOperator();

        if ($operator === '=') {
            if ($countedValue === $floatConditionValue) {
                return true;
            }
        } elseif ($operator === '>') {
            if ($countedValue > $floatConditionValue) {
                return true;
            }
        } elseif ($operator === '>=') {
            if ($countedValue >= $floatConditionValue) {
                return true;
            }
        } elseif ($operator === '<') {
            if ($countedValue < $floatConditionValue) {
                return true;
            }
        } elseif ($operator === '<=') {
            if ($countedValue <= $floatConditionValue) {
                return true;
            }
        } elseif ($operator === '!=' || $operator === '<>') {
            if ($countedValue !== $floatConditionValue) {
                return true;
            }
        } elseif ($operator === 'IN') {
            $newValues = [];

            foreach ($conditionValue as $value) {
                $newValues[] = (float)$value;
            }

            if (in_array($countedValue, $newValues, true)) {
                return true;
            }
        } elseif ($operator === 'NOT IN') {
            $newValues = [];

            foreach ($conditionValue as $value) {
                $newValues[] = (float)$value;
            }

            if (!in_array($countedValue, $newValues, true)) {
                return true;
            }
        } elseif ($operator === 'BETWEEN') {
            if ($countedValue > $conditionValue[0] && $countedValue < $conditionValue[1]) {
                return true;
            }
        } elseif ($operator === 'BETWEEN_INCLUSIVE') {
            if ($countedValue >= $conditionValue[0] && $countedValue <= $conditionValue[1]) {
                return true;
            }
        } else {
            throw new Exception('Unknown operator');
        }

        return false;
    }
}
