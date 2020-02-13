<?php

namespace pql\QueryExecutor;

use Exception;
use pql\AggregateFunction;
use pql\Condition;
use pql\Operator;
use pql\QueryBuilder\Query;

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

        $isBetweenOperator = $condition->getOperator() === Operator::BETWEEN || $condition->getOperator() === Operator::BETWEEN_INCLUSIVE;

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
            if ($condition->getOperator() === Operator::IN) {
                $issetRowAColumn = false;
                $issetRowAValue = true;
            } elseif ($isBetweenOperator) {
                $issetRowAColumn = true;
                $issetRowAValue = false;
            }

            $issetRowAColumnRowBValue = false;
            $issetRowAValueRowBColumn = false;
        } elseif ($isValueArray) {
            if ($condition->getOperator() === Operator::IN) {
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
            $issetRowAColumn = isset($rowA[$condition->getColumn()]);
            $issetRowAValue = isset($rowA[$condition->getValue()]);
            $issetRowAColumnRowBValue = isset($rowA[$condition->getColumn()], $rowB[$condition->getValue()]);
            $issetRowAValueRowBColumn = isset($rowA[$condition->getValue()], $rowB[$condition->getColumn()]);
        }

        switch ($condition->getOperator()) {
            case Operator::EQUAL:
                // column = 5
                if ($issetRowAColumn && $rowA[$condition->getColumn()] === $condition->getValue()) {
                    return true;
                }

                // 5 = column
                if ($issetRowAValue && $rowA[$condition->getValue()] === $condition->getColumn()) {
                    return true;
                }

                // column1 = column2
                if ($issetRowAColumnRowBValue && $rowA[$condition->getColumn()] === $rowB[$condition->getValue()]) {
                    return true;
                }

                // column2 = column1
                if ($issetRowAValueRowBColumn && $rowA[$condition->getValue()] === $rowB[$condition->getColumn()]) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) = column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getColumn());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getValue()] === $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                /**
                 * column = (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getColumn()] === $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                // COUNT(column_id) = 1
                if ($columnIsFunction) {
                    if ($rowA[(string)$condition->getColumn()] === $condition->getValue()) {
                        return true;
                    }
                }

                // 1 = COUNT(column_id)
                if ($valueIsFunction) {
                    if ($valueIsFunction && $rowA[(string)$condition->getValue()] === $condition->getColumn()) {
                        return true;
                    }
                }

                break;

            case Operator::GREATER_THAN:
                // column > 5
                if ($issetRowAColumn && $rowA[$condition->getColumn()] > $condition->getValue()) {
                    return true;
                }

                // 5 > column
                if ($issetRowAValue && $rowA[$condition->getValue()] > $condition->getColumn()) {
                    return true;
                }

                // column1 > column2
                if ($issetRowAColumnRowBValue && $rowA[$condition->getColumn()] > $rowB[$condition->getValue()]) {
                    return true;
                }

                // column2 > column1
                if ($issetRowAValueRowBColumn && $rowA[$condition->getValue()] > $rowB[$condition->getColumn()]) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) > column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getColumn());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getValue()] > $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                /**
                 * column > (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getColumn()] > $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                // COUNT(column_id) > 1
                if ($columnIsFunction) {
                    if ($rowA[(string)$condition->getColumn()] > $condition->getValue()) {
                        return true;
                    }
                }

                // 1 > COUNT(column_id)
                if ($valueIsFunction) {
                    if ($valueIsFunction && $rowA[(string)$condition->getValue()] > $condition->getColumn()) {
                        return true;
                    }
                }

                break;

            case Operator::GREATER_EQUAL_THAN:
                // column >= 5
                if ($issetRowAColumn && $rowA[$condition->getColumn()] >= $condition->getValue()) {
                    return true;
                }

                // 5 >= column
                if ($issetRowAValue && $rowA[$condition->getValue()] >= $condition->getColumn()) {
                    return true;
                }

                // column1 >= column2
                if ($issetRowAColumnRowBValue && $rowA[$condition->getColumn()] >= $rowB[$condition->getValue()]) {
                    return true;
                }

                // column2 = column1
                if ($issetRowAValueRowBColumn && $rowA[$condition->getValue()] >= $rowB[$condition->getColumn()]) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) >= column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getColumn());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getValue()] >= $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                /**
                 * column >= (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getColumn()] >= $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                // COUNT(column_id) >= 1
                if ($columnIsFunction) {
                    if ($rowA[(string)$condition->getColumn()] >= $condition->getValue()) {
                        return true;
                    }
                }

                // 1 >= COUNT(column_id)
                if ($valueIsFunction) {
                    if ($valueIsFunction && $rowA[(string)$condition->getValue()] >= $condition->getColumn()) {
                        return true;
                    }
                }

                break;

            case Operator::LESS_THAN:
                // column < 5
                if ($issetRowAColumn && $rowA[$condition->getColumn()] < $condition->getValue()) {
                    return true;
                }

                // 5 < column
                if ($issetRowAValue && $rowA[$condition->getValue()] < $condition->getColumn()) {
                    return true;
                }

                // column1 < column2
                if ($issetRowAColumnRowBValue && $rowA[$condition->getColumn()] < $rowB[$condition->getValue()]) {
                    return true;
                }

                // column2 < column1
                if ($issetRowAValueRowBColumn && $rowA[$condition->getValue()] < $rowB[$condition->getColumn()]) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) < column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getColumn());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getValue()] < $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                /**
                 * column < (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getColumn()] < $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                // COUNT(column_id) < 1
                if ($columnIsFunction) {
                    if ($rowA[(string)$condition->getColumn()] < $condition->getValue()) {
                        return true;
                    }
                }

                // 1 < COUNT(column_id)
                if ($valueIsFunction) {
                    if ($valueIsFunction && $rowA[(string)$condition->getValue()] < $condition->getColumn()) {
                        return true;
                    }
                }

                break;

            case Operator::LESS_EQUAL_THAN:
                // column <= 5
                if ($issetRowAColumn && $rowA[$condition->getColumn()] <= $condition->getValue()) {
                    return true;
                }

                // 5 <= column
                if ($issetRowAValue && $rowA[$condition->getValue()] <= $condition->getColumn()) {
                    return true;
                }

                // column1 <= column2
                if ($issetRowAColumnRowBValue && $rowA[$condition->getColumn()] <= $rowB[$condition->getValue()]) {
                    return true;
                }

                // column2 <= column1
                if ($issetRowAValueRowBColumn && $rowA[$condition->getValue()] <= $rowB[$condition->getColumn()]) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) <= column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getColumn());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getValue()] <= $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                /**
                 * column <= (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getColumn()] <= $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                // COUNT(column_id) <= 1
                if ($columnIsFunction) {
                    if ($rowA[(string)$condition->getColumn()] <= $condition->getValue()) {
                        return true;
                    }
                }

                // 1 <= COUNT(column_id)
                if ($valueIsFunction) {
                    if ($valueIsFunction && $rowA[(string)$condition->getValue()] <= $condition->getColumn()) {
                        return true;
                    }
                }

                break;

            case Operator::IN:
                // column IN (5)
                if ($issetRowAColumn && in_array($rowA[$condition->getColumn()], $condition->getValue(), true)) {
                    return true;
                }

                // (5) IN column
                if ($issetRowAValue && in_array($rowA[$condition->getValue()], $condition->getColumn(), true)) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) IN column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneColumn($condition->getColumn());
                    $firstColumn    = $subQueryResult->getColumns()[0];

                    foreach ($subQueryResult->getRows() as $subRows) {
                        if ($rowA[$condition->getColumn()] === $subRows->get()->{$firstColumn}) {
                            return true;
                        }
                    }
                }

                /**
                 * column IN (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneColumn($condition->getValue());
                    $firstColumn    = $subQueryResult->getColumns()[0];

                    foreach ($subQueryResult->getRows() as $subRows) {
                        if ($rowA[$condition->getColumn()] === $subRows->get()->{$firstColumn}) {
                            return true;
                        }
                    }
                }

                break;

            case Operator::NOT_IN:
                // column NOT IN (5)
                if ($issetRowAColumn && !in_array($rowA[$condition->getColumn()], $condition->getValue(), true)) {
                    return true;
                }

                // (5) NOT IN column
                if ($issetRowAValue && !in_array($rowA[$condition->getValue()], $condition->getColumn(), true)) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) NOT IN column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneColumn($condition->getColumn());
                    $firstColumn    = $subQueryResult->getColumns()[0];

                    foreach ($subQueryResult->getRows() as $subRows) {
                        if ($rowA[$condition->getColumn()] !== $subRows->get()->{$firstColumn}) {
                            return true;
                        }
                    }
                }

                /**
                 * column NOT IN (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneColumn($condition->getValue());
                    $firstColumn    = $subQueryResult->getColumns()[0];

                    foreach ($subQueryResult->getRows() as $subRows) {
                        if ($rowA[$condition->getColumn()] !== $subRows->get()->{$firstColumn}) {
                            return true;
                        }
                    }
                }

                break;

            case Operator::BETWEEN:
                if (!$issetRowAColumn && $rowA[$condition->getColumn()] > $condition->getValue()[0] && $rowA[$condition->getColumn()] < $condition->getValue()[1]) {
                    return true;
                }

                if (!$issetRowAValue && $rowA[$condition->getValue()] > $condition->getColumn()[0] && $rowA[$condition->getValue()] < $condition->getColumn()[1]) {
                    return true;
                }

                break;

            case Operator::BETWEEN_INCLUSIVE:
                if (!$issetRowAColumn && $rowA[$condition->getColumn()] >= $condition->getValue()[0] && $rowA[$condition->getColumn()] <= $condition->getValue()[1]) {
                    return true;
                }

                if (!$issetRowAValue && $rowA[$condition->getValue()] >= $condition->getColumn()[0] && $rowA[$condition->getValue()] <= $condition->getColumn()[1]) {
                    return true;
                }

                break;

            case Operator::NON_EQUAL:
            case Operator::LESS_AND_GREATER_THAN:
                // column != 5
                if ($issetRowAColumn && $rowA[$condition->getColumn()] !== $condition->getValue()) {
                    return true;
                }

                // 5 != column
                if ($issetRowAValue && $rowA[$condition->getValue()] !== $condition->getColumn()) {
                    return true;
                }

                // column1 != column2
                if ($issetRowAColumnRowBValue && $rowA[$condition->getColumn()] !== $rowB[$condition->getValue()]) {
                    return true;
                }

                // column2 != column1
                if ($issetRowAValueRowBColumn && $rowA[$condition->getValue()] !== $rowB[$condition->getColumn()]) {
                    return true;
                }

                /**
                 * (SELECT id from table ....) != column
                 */
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getColumn());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getValue()] !== $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                /**
                 * column != (SELECT id from table ....)
                 */
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue());

                    $firstRow    = $subQueryResult->getRows()[0];
                    $firstColumn = $subQueryResult->getColumns()[0];

                    if ($rowA[$condition->getColumn()] !== $firstRow->get()->{$firstColumn}) {
                        return true;
                    }
                }

                // COUNT(column_id) != 1
                if ($columnIsFunction) {
                    if ($rowA[(string)$condition->getColumn()] !== $condition->getValue()) {
                        return true;
                    }
                }

                // 1 != COUNT(column_id)
                if ($valueIsFunction) {
                    if ($valueIsFunction && $rowA[(string)$condition->getValue()] !== $condition->getColumn()) {
                        return true;
                    }
                }

                break;

            case Operator::REGULAR_EXPRESSION:
                $quotedValue  = preg_quote($condition->getValue(), '#');
                $quotedColumn = preg_quote($condition->getColumn(), '#');

                // column regexp [a-z]
                if ($issetRowAColumn && preg_match('#' . $quotedValue . '#', $rowA[$condition->getColumn()])) {
                    return true;
                }

                // [a-z] regexp column
                if ($issetRowAValue && preg_match('#' . $quotedColumn . '#', $rowA[$condition->getValue()])) {
                    return true;
                }

                break;

            case Operator::IS_NULL:
                // column IS NULL
                if ($issetRowAColumn && $rowA[$condition->getColumn()] === 'null') {
                    return true;
                }

                // IS NULL column
                if ($issetRowAValue && $rowA[$condition->getValue()] === 'null') {
                    return true;
                }

                break;

            case Operator::IS_NOT_NULL:
                // column IS NOT NULL
                if ($issetRowAColumn && $rowA[$condition->getColumn()] !== 'null') {
                    return true;
                }

                // IS NOT NULL column
                if ($issetRowAValue && $rowA[$condition->getValue()] !== 'null') {
                    return true;
                }

                break;

            case Operator::EXISTS:
                // WHERE EXISTS (SELECT id from table ....)
                if ($hasSubQueryColumn) {
                    $subQueryResult = SubQueryHelper::runAndCheckSubQuery($condition->getColumn());

                    if ($subQueryResult->getRowsCount()) {
                        return true;
                    }
                }

                // WHERE (SELECT id from table ....) EXISTS
                if ($hasSubQueryValue) {
                    $subQueryResult = SubQueryHelper::runAndCheckSubQuery($condition->getValue());

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

    /**
     * @param Condition $condition
     * @param mixed     $value
     *
     * @return bool
     */
    public static function havingCondition(Condition $condition, $value)
    {
        if ($condition->getOperator() === Operator::EQUAL && $value === $condition->getValue()) {
            return true;
        }

        if ($condition->getOperator() === Operator::LESS_THAN && $value < $condition->getValue()) {
            return true;
        }

        if ($condition->getOperator() === Operator::LESS_EQUAL_THAN && $value <= $condition->getValue()) {
            return true;
        }

        if ($condition->getOperator() === Operator::GREATER_THAN && $value > $condition->getValue()) {
            return true;
        }

        if ($condition->getOperator() === Operator::GREATER_EQUAL_THAN && $value >= $condition->getValue()) {
            return true;
        }

        if (($condition->getOperator() === Operator::LESS_AND_GREATER_THAN || $condition->getOperator() === Operator::NON_EQUAL) && $value !== $condition->getValue()) {
            return true;
        }

        if ($condition->getOperator() === Operator::BETWEEN && $value > $condition->getValue() && $value < $condition->getValue()) {
            return true;
        }

        if ($condition->getOperator() === Operator::BETWEEN_INCLUSIVE && $value >= $condition->getValue() && $value <= $condition->getValue()) {
            return true;
        }

        return false;
    }
}
