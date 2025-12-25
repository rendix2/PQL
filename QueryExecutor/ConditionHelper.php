<?php

namespace pql\QueryExecutor;

use Exception;
use pql\Condition;
use pql\Operator;
use pql\QueryBuilder\Operator\Between;
use pql\QueryBuilder\Operator\BetweenInclusive;
use pql\QueryBuilder\Operator\Equals;
use pql\QueryBuilder\Operator\Exists;
use pql\QueryBuilder\Operator\In;
use pql\QueryBuilder\Operator\IsNotNull;
use pql\QueryBuilder\Operator\IsNull;
use pql\QueryBuilder\Operator\Larger;
use pql\QueryBuilder\Operator\LargerInclusive;
use pql\QueryBuilder\Operator\NotEquals;
use pql\QueryBuilder\Operator\NotIn;
use pql\QueryBuilder\Operator\RegularExpression;
use pql\QueryBuilder\Operator\Smaller;
use pql\QueryBuilder\Operator\SmallerInclusive;
use pql\QueryBuilder\Query;
use pql\QueryBuilder\Select\AggregateFunction;

/**
 * Class ConditionHelper
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class ConditionHelper
{
    /**
     * @param Condition $condition
     * @param array     $rowA
     * @param array     $rowB
     *
     * @return bool
     * @throws Exception
     */
    public static function condition(Condition $condition, array $rowA, array $rowB)
    {
        $hasSubQueryValue  = $condition->getValue() instanceof Query;
        $hasSubQueryColumn = $condition->getColumn() instanceof Query;

        $isValueArray  = is_array($condition->getValue());
        $isColumnArray = is_array($condition->getColumn());

        $operator = $condition->getOperator()->evaluate();

        $isBetweenOperator = $operator === Operator::BETWEEN || $operator === Operator::BETWEEN_INCLUSIVE;

        $columnIsFunction = $condition->getColumn() instanceof AggregateFunction;
        $valueIsFunction  = $condition->getValue() instanceof AggregateFunction;

        $issetRowAColumn = null;
        $issetRowAValue  = null;

        // set flags
        if ($hasSubQueryColumn || $hasSubQueryValue) {
            $issetRowAColumn = false;
            $issetRowAValue = false;
            $issetRowAColumnRowBValue = false;
            $issetRowAValueRowBColumn = false;
        } elseif ($isColumnArray) {
            if ($operator === Operator::IN) {
                $issetRowAColumn = false;
                $issetRowAValue = true;
            } elseif ($isBetweenOperator) {
                $issetRowAColumn = true;
                $issetRowAValue = false;
            }

            $issetRowAColumnRowBValue = false;
            $issetRowAValueRowBColumn = false;
        } elseif ($isValueArray) {
            if ($operator === Operator::IN) {
                $issetRowAColumn = true;
                $issetRowAValue = false;
            } elseif ($isBetweenOperator) {
                $issetRowAColumn = false;
                $issetRowAValue = true;
            }

            $issetRowAColumnRowBValue = false;
            $issetRowAValueRowBColumn = false;
        } elseif ($columnIsFunction || $valueIsFunction) {
            $issetRowAColumn = false;
            $issetRowAValue = false;
            $issetRowAColumnRowBValue = false;
            $issetRowAValueRowBColumn = false;
        } else {
            $issetRowAColumn = isset($rowA[$condition->getColumn()->evaluate()]);
            $issetRowAValue = isset($rowA[$condition->getValue()->evaluate()]);
            $issetRowAColumnRowBValue = isset($rowA[$condition->getColumn()->evaluate()], $rowB[$condition->getValue()->evaluate()]);
            $issetRowAValueRowBColumn = isset($rowA[$condition->getValue()->evaluate()], $rowB[$condition->getColumn()->evaluate()]);
        }

        $operator = get_class($condition->getOperator());

        switch ($operator) {
            case Equals::class:
                // column = 5
                if ($issetRowAColumn && $rowA[$condition->getColumn()->evaluate()] === $condition->getValue()->evaluate()) {
                    return true;
                }

                // 5 = column
                if ($issetRowAValue && $rowA[$condition->getValue()->evaluate()] === $condition->getColumn()) {
                    return true;
                }

                // column1 = column2
                if ($issetRowAColumnRowBValue && $rowA[$condition->getColumn()->evaluate()] === $rowB[$condition->getValue()->evaluate()]) {
                    return true;
                }

                // column2 = column1
                if ($issetRowAValueRowBColumn && $rowA[$condition->getValue()->evaluate()] === $rowB[$condition->getColumn()->evaluate()]) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) = column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getColumn()->getQuery());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getValue()->evaluate()] === $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                /**
                 * column = (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue()->getQuery());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getColumn()->evaluate()] === $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                // COUNT(column_id) = 1
                if ($columnIsFunction) {
                    if ($rowA[(string)$condition->getColumn()->evaluate()] === $condition->getValue()) {
                        return true;
                    }
                }

                // 1 = COUNT(column_id)
                if ($valueIsFunction) {
                    if ($rowA[(string)$condition->getValue()->evaluate()] === $condition->getColumn()) {
                        return true;
                    }
                }

                break;

            case Larger::class:
                // column > 5
                if ($issetRowAColumn && $rowA[$condition->getColumn()->evaluate()] > $condition->getValue()->evaluate()) {
                    return true;
                }

                // 5 > column
                if ($issetRowAValue && $rowA[$condition->getValue()->evaluate()] > $condition->getColumn()) {
                    return true;
                }

                // column1 > column2
                if ($issetRowAColumnRowBValue && $rowA[$condition->getColumn()->evaluate()] > $rowB[$condition->getValue()->evaluate()]) {
                    return true;
                }

                // column2 > column1
                if ($issetRowAValueRowBColumn && $rowA[$condition->getValue()->evaluate()] > $rowB[$condition->getColumn()->evaluate()]) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) > column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getColumn()->evaluate()->evaluate());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getValue()->evaluate()] > $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                /**
                 * column > (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue()->getQuery());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getColumn()->evaluate()] > $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                // COUNT(column_id) > 1
                if ($columnIsFunction) {
                    if ($rowA[(string)$condition->getColumn()->evaluate()] > $condition->getValue()) {
                        return true;
                    }
                }

                // 1 > COUNT(column_id)
                if ($valueIsFunction) {
                    if ($rowA[(string)$condition->getValue()->evaluate()] > $condition->getColumn()) {
                        return true;
                    }
                }

                break;

            case LargerInclusive::class:
                // column >= 5
                if ($issetRowAColumn && $rowA[$condition->getColumn()->evaluate()] >= $condition->getValue()->evaluate()) {
                    return true;
                }

                // 5 >= column
                if ($issetRowAValue && $rowA[$condition->getValue()->evaluate()] >= $condition->getColumn()) {
                    return true;
                }

                // column1 >= column2
                if ($issetRowAColumnRowBValue && $rowA[$condition->getColumn()->evaluate()] >= $rowB[$condition->getValue()->evaluate()]) {
                    return true;
                }

                // column2 = column1
                if ($issetRowAValueRowBColumn && $rowA[$condition->getValue()->evaluate()] >= $rowB[$condition->getColumn()->evaluate()]) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) >= column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getColumn()->getQuery());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getValue()->evaluate()] >= $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                /**
                 * column >= (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue()->getQuery());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getColumn()->evaluate()] >= $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                // COUNT(column_id) >= 1
                if ($columnIsFunction) {
                    if ($rowA[(string)$condition->getColumn()->evaluate()] >= $condition->getValue()) {
                        return true;
                    }
                }

                // 1 >= COUNT(column_id)
                if ($valueIsFunction) {
                    if ($rowA[(string)$condition->getValue()->evaluate()] >= $condition->getColumn()) {
                        return true;
                    }
                }

                break;

            case Smaller::class:
                // column < 5
                if ($issetRowAColumn && $rowA[$condition->getColumn()->evaluate()] < $condition->getValue()->evaluate()) {
                    return true;
                }

                // 5 < column
                if ($issetRowAValue && $rowA[$condition->getValue()->evaluate()] < $condition->getColumn()) {
                    return true;
                }

                // column1 < column2
                if ($issetRowAColumnRowBValue && $rowA[$condition->getColumn()->evaluate()] < $rowB[$condition->getValue()->evaluate()]) {
                    return true;
                }

                // column2 < column1
                if ($issetRowAValueRowBColumn && $rowA[$condition->getValue()->evaluate()] < $rowB[$condition->getColumn()->evaluate()]) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) < column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getColumn()->getQuery());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getValue()->evaluate()] < $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                /**
                 * column < (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue()->getQuery());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getColumn()->evaluate()] < $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                // COUNT(column_id) < 1
                if ($columnIsFunction) {
                    if ($rowA[(string)$condition->getColumn()->evaluate()] < $condition->getValue()) {
                        return true;
                    }
                }

                // 1 < COUNT(column_id)
                if ($valueIsFunction) {
                    if ($valueIsFunction && $rowA[(string)$condition->getValue()->evaluate()] < $condition->getColumn()) {
                        return true;
                    }
                }

                break;

            case SmallerInclusive::class:
                // column <= 5
                if ($issetRowAColumn && $rowA[$condition->getColumn()->evaluate()] <= $condition->getValue()) {
                    return true;
                }

                // 5 <= column
                if ($issetRowAValue && $rowA[$condition->getValue()->evaluate()] <= $condition->getColumn()) {
                    return true;
                }

                // column1 <= column2
                if ($issetRowAColumnRowBValue && $rowA[$condition->getColumn()->evaluate()] <= $rowB[$condition->getValue()->evaluate()]) {
                    return true;
                }

                // column2 <= column1
                if ($issetRowAValueRowBColumn && $rowA[$condition->getValue()->evaluate()] <= $rowB[$condition->getColumn()->evaluate()]) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) <= column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getColumn()->getQuery());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getValue()->evaluate()] <= $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                /**
                 * column <= (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue()->getQuery());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getColumn()->evaluate()] <= $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                // COUNT(column_id) <= 1
                if ($columnIsFunction) {
                    if ($rowA[(string)$condition->getColumn()->evaluate()] <= $condition->getValue()) {
                        return true;
                    }
                }

                // 1 <= COUNT(column_id)
                if ($valueIsFunction) {
                    if ($rowA[(string)$condition->getValue()->evaluate()] <= $condition->getColumn()) {
                        return true;
                    }
                }

                break;

            case In::class:
                // column IN (5)
                if ($issetRowAColumn && in_array($rowA[$condition->getColumn()->evaluate()], $condition->getValue()->getValues(), true)) {
                    return true;
                }

                // (5) IN column
                if ($issetRowAValue && in_array($rowA[$condition->getValue()->evaluate()], $condition->getColumn()->getValues(), true)) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) IN column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneColumn($condition->getColumn()->getQuery());
                    $firstColumn    = $subQueryResult->getColumns()[0];

                    foreach ($subQueryResult->getRows() as $subRows) {
                        if ($rowA[$condition->getColumn()->evaluate()] === $subRows->get()->{$firstColumn}) {
                            return true;
                        }
                    }
                }

                /**
                 * column IN (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneColumn($condition->getValue()->getQuery());
                    $firstColumn    = $subQueryResult->getColumns()[0];

                    foreach ($subQueryResult->getRows() as $subRows) {
                        if ($rowA[$condition->getColumn()->evaluate()] === $subRows->get()->{$firstColumn}) {
                            return true;
                        }
                    }
                }

                break;

            case NotIn::class:
                // column NOT IN (5)
                if ($issetRowAColumn && !in_array($rowA[$condition->getColumn()->evaluate()], $condition->getValue()->getValues(), true)) {
                    return true;
                }

                // (5) NOT IN column
                if ($issetRowAValue && !in_array($rowA[$condition->getValue()->evaluate()], $condition->getColumn()->getValues(), true)) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) NOT IN column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneColumn($condition->getColumn()->getQuery());
                    $firstColumn    = $subQueryResult->getColumns()[0];

                    foreach ($subQueryResult->getRows() as $subRows) {
                        if ($rowA[$condition->getColumn()->evaluate()] !== $subRows->get()->{$firstColumn}) {
                            return true;
                        }
                    }
                }

                /**
                 * column NOT IN (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneColumn($condition->getValue()->getQuery());
                    $firstColumn    = $subQueryResult->getColumns()[0];

                    foreach ($subQueryResult->getRows() as $subRows) {
                        if ($rowA[$condition->getColumn()->evaluate()] !== $subRows->get()->{$firstColumn}) {
                            return true;
                        }
                    }
                }

                break;

            case Between::class:
                if (!$issetRowAColumn && $rowA[$condition->getColumn()->evaluate()] > $condition->getValue()[0] && $rowA[$condition->getColumn()->evaluate()] < $condition->getValue()[1]) {
                    return true;
                }

                if (!$issetRowAValue && $rowA[$condition->getValue()->evaluate()] > $condition->getColumn()[0] && $rowA[$condition->getValue()->evaluate()] < $condition->getColumn()[1]) {
                    return true;
                }

                break;

            case BetweenInclusive::class:
                if (!$issetRowAColumn && $rowA[$condition->getColumn()->evaluate()] >= $condition->getValue()[0] && $rowA[$condition->getColumn()->evaluate()] <= $condition->getValue()[1]) {
                    return true;
                }

                if (!$issetRowAValue && $rowA[$condition->getValue()->evaluate()] >= $condition->getColumn()[0] && $rowA[$condition->getValue()->evaluate()] <= $condition->getColumn()[1]) {
                    return true;
                }

                break;

            case NotEquals::class:
                // column != 5
                if ($issetRowAColumn && $rowA[$condition->getColumn()->evaluate()] !== $condition->getValue()) {
                    return true;
                }

                // 5 != column
                if ($issetRowAValue && $rowA[$condition->getValue()->evaluate()] !== $condition->getColumn()) {
                    return true;
                }

                // column1 != column2
                if ($issetRowAColumnRowBValue && $rowA[$condition->getColumn()->evaluate()] !== $rowB[$condition->getValue()->evaluate()]) {
                    return true;
                }

                // column2 != column1
                if ($issetRowAValueRowBColumn && $rowA[$condition->getValue()->evaluate()] !== $rowB[$condition->getColumn()->evaluate()]) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) != column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getColumn()->getQuery());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getValue()->evaluate()] !== $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                /**
                 * column != (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue()->getQuery());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getColumn()->evaluate()] !== $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                // COUNT(column_id) != 1
                if ($columnIsFunction) {
                    if ($rowA[(string)$condition->getColumn()->evaluate()] !== $condition->getValue()) {
                        return true;
                    }
                }

                // 1 != COUNT(column_id)
                if ($valueIsFunction) {
                    if ($rowA[(string)$condition->getValue()->evaluate()] !== $condition->getColumn()) {
                        return true;
                    }
                }

                break;

            case RegularExpression::class:
                $quotedValue  = preg_quote($condition->getValue()->evaluate(), '#');
                $quotedColumn = preg_quote($condition->getColumn()->evaluate(), '#');

                // column regexp [a-z]
                if ($issetRowAColumn && preg_match('#' . $quotedValue . '#', $rowA[$condition->getColumn()->evaluate()])) {
                    return true;
                }

                // [a-z] regexp column
                if ($issetRowAValue && preg_match('#' . $quotedColumn . '#', $rowA[$condition->getValue()->evaluate()])) {
                    return true;
                }

                break;

            case IsNull::class:
                // column IS NULL
                if ($issetRowAColumn && $rowA[$condition->getColumn()->evaluate()] === 'null') {
                    return true;
                }

                // IS NULL column
                if ($issetRowAValue && $rowA[$condition->getValue()->evaluate()] === 'null') {
                    return true;
                }

                break;

            case IsNotNull::class:
                // column IS NOT NULL
                if ($issetRowAColumn && $rowA[$condition->getColumn()->evaluate()] !== 'null') {
                    return true;
                }

                // IS NOT NULL column
                if ($issetRowAValue && $rowA[$condition->getValue()->evaluate()] !== 'null') {
                    return true;
                }

                break;

            case Exists::class:
                // WHERE EXISTS (SELECT id from table ....)
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckSubQuery($condition->getColumn()->getQuery());

                    if ($subQueryResult->getRowsCount()) {
                        return true;
                    }
                }

                // WHERE (SELECT id from table ....) EXISTS
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckSubQuery($condition->getValue()->getQuery());

                    if ($subQueryResult->getRowsCount()) {
                        return true;
                    }
                }

                break;

            default:
                $message = sprintf('Unknown "%s" operator.', $condition->getOperator());

                throw new Exception($message);
        }

        return false;
    }

    public static function havingCondition(Condition $condition, mixed $value): bool
    {
        $operator = $condition->getOperator()->evaluate();

        if ($operator === Operator::EQUAL && $value === $condition->getValue()) {
            return true;
        }

        if ($operator === Operator::NON_EQUAL && $value !== $condition->getValue()) {
            return true;
        }

        if ($operator === Operator::LESS_THAN && $value < $condition->getValue()) {
            return true;
        }

        if ($operator === Operator::LESS_EQUAL_THAN && $value <= $condition->getValue()) {
            return true;
        }

        if ($operator === Operator::GREATER_THAN && $value > $condition->getValue()) {
            return true;
        }

        if ($operator === Operator::GREATER_EQUAL_THAN && $value >= $condition->getValue()) {
            return true;
        }

        if ($operator === Operator::BETWEEN && $value > $condition->getValue() && $value < $condition->getValue()->evaluate()) {
            return true;
        }

        if ($operator === Operator::BETWEEN_INCLUSIVE && $value >= $condition->getValue() && $value <= $condition->getValue()->evaluate()) {
            return true;
        }

        return false;
    }
}
