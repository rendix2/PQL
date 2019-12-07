<?php

namespace query;

use Condition;
use Operator;
use Query;
use SubQueryHelper;
use Table;

/**
 * Class ConditionHelper
 *
 * @package query
 */
class ConditionHelper
{
    /**
     * @param Condition $condition
     * @param array     $rowA
     * @param array     $rowB
     *
     * @return bool
     */
    public static function condition(Condition $condition, array $rowA, array $rowB)
    {
        $hasSubQueryValue = $condition->getValue() instanceof Query;
        $hasSubQueryColumn = $condition->getColumn() instanceof Query;
        $isValueArray = is_array($condition->getValue());
        $isColumnArray = is_array($condition->getColumn());

        // set flags
        if ($hasSubQueryColumn || $hasSubQueryValue) {
            $issetRowAColumn = false;
            $issetRowAValue = false;
            $issetRowAColumnRowBValue = false;
            $issetRowAValueRowBColumn = false;
        } elseif ($isColumnArray) {
            if ($condition->getOperator() === 'in') {
                $issetRowAColumn = false;
                $issetRowAValue = true;
            } elseif ($condition->getOperator() === 'between' || $condition->getOperator() === 'between_in') {
                $issetRowAColumn = true;
                $issetRowAValue = false;
            }

            $issetRowAColumnRowBValue = false;
            $issetRowAValueRowBColumn = false;
        } elseif ($isValueArray) {
            if ($condition->getOperator() === 'in') {
                $issetRowAColumn = true;
                $issetRowAValue = false;
            } elseif ($condition->getOperator() === 'between' || $condition->getOperator() === 'between_in') {
                $issetRowAColumn = false;
                $issetRowAValue = true;
            }

            $issetRowAColumnRowBValue = false;
            $issetRowAValueRowBColumn = false;
        } else {
            $issetRowAColumn = isset($rowA[$condition->getColumn()]);
            $issetRowAValue = isset($rowA[$condition->getValue()]);
            $issetRowAColumnRowBValue = isset($rowA[$condition->getColumn()], $rowB[$condition->getValue()]);
            $issetRowAValueRowBColumn = isset($rowA[$condition->getValue()], $rowB[$condition->getColumn()]);
        }

        if ($condition->getOperator() === Operator::EQUAL) {
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

                $firstRow = $subQueryResult->getRows()[0];
                $firstColumn = $subQueryResult->getColumns()[0];

                if ($rowA[$condition->getValue()]  === $firstRow->get()->{$firstColumn}) {
                    return true;
                }
            }

            /**
             * column = (SELECT id from table ....)
             */
            if ($hasSubQueryValue) {
                $subQueryResult = SubQueryHelper::runAndCheckOneRowOneColumn($condition->getValue());

                $firstRow = $subQueryResult->getRows()[0];
                $firstColumn = $subQueryResult->getColumns()[0];

                if ($rowA[$condition->getColumn()]  === $firstRow->get()->{$firstColumn}) {
                    return true;
                }
            }
        }

        if ($condition->getOperator() === '>') {
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

                $firstRow = $subQueryResult->getRows()[0];
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

                $firstRow = $subQueryResult->getRows()[0];
                $firstColumn = $subQueryResult->getColumns()[0];

                if ($rowA[$condition->getColumn()] > $firstRow->get()->{$firstColumn}) {
                    return true;
                }
            }
        }

        if ($condition->getOperator() === '>=') {
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

                $firstRow = $subQueryResult->getRows()[0];
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

                $firstRow = $subQueryResult->getRows()[0];
                $firstColumn = $subQueryResult->getColumns()[0];

                if ($rowA[$condition->getColumn()] >= $firstRow->get()->{$firstColumn}) {
                    return true;
                }
            }
        }

        if ($condition->getOperator() === '<') {
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

                $firstRow = $subQueryResult->getRows()[0];
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

                $firstRow = $subQueryResult->getRows()[0];
                $firstColumn = $subQueryResult->getColumns()[0];

                if ($rowA[$condition->getColumn()] < $firstRow->get()->{$firstColumn}) {
                    return true;
                }
            }
        }

        if ($condition->getOperator() === '<=') {
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

                $firstRow = $subQueryResult->getRows()[0];
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

                $firstRow = $subQueryResult->getRows()[0];
                $firstColumn = $subQueryResult->getColumns()[0];

                if ($rowA[$condition->getColumn()] <= $firstRow->get()->{$firstColumn}) {
                    return true;
                }
            }
        }

        if ($condition->getOperator() === '!=' || $condition->getOperator() === '<>') {
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

                $firstRow = $subQueryResult->getRows()[0];
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

                $firstRow = $subQueryResult->getRows()[0];
                $firstColumn = $subQueryResult->getColumns()[0];

                if ($rowA[$condition->getColumn()] !== $firstRow->get()->{$firstColumn}) {
                    return true;
                }
            }
        }

        if ($condition->getOperator() === 'in') {
            // column IN (5)
            if ($issetRowAColumn && in_array($rowA[$condition->getColumn()], $condition->getValue(), true)) {
                return true;
            }

            // (5) IN column
            if ($issetRowAValue && in_array($rowA[$condition->getValue()], $condition->getColumn(), true)) {
                return true;
            }

            /**
             * (SELECT id from table ....) = column
             */
            if ($hasSubQueryColumn) {
                $subQueryResult = SubQueryHelper::runAndCheckOneColumn($condition->getColumn());
                $firstColumn = $subQueryResult->getColumns()[0];

                foreach ($subQueryResult->getRows() as $subRows) {
                    if ($rowA[$condition->getColumn()]  === $subRows->get()->{$firstColumn}) {
                        return true;
                    }
                }
            }

            /**
             * column = (SELECT id from table ....)
             */
            if ($hasSubQueryValue) {
                $subQueryResult = SubQueryHelper::runAndCheckOneColumn($condition->getValue());
                $firstColumn = $subQueryResult->getColumns()[0];

                foreach ($subQueryResult->getRows() as $subRows) {
                    if ($rowA[$condition->getColumn()]  === $subRows->get()->{$firstColumn}) {
                        return true;
                    }
                }
            }
        }

        if ($condition->getOperator() === 'between') {
            if (!$issetRowAColumn &&
                $rowA[$condition->getColumn()] > $condition->getValue()[0] &&
                $rowA[$condition->getColumn()] < $condition->getValue()[1]
            ) {
                return true;
            }

            if (!$issetRowAValue &&
                $rowA[$condition->getValue()] > $condition->getColumn()[0] &&
                $rowA[$condition->getValue()] < $condition->getColumn()[1]
            ) {
                return true;
            }
        }

        if ($condition->getOperator() === 'between_in') {
            if (!$issetRowAColumn &&
                $rowA[$condition->getColumn()] >= $condition->getValue()[0] &&
                $rowA[$condition->getColumn()] <= $condition->getValue()[1]
            ) {
                return true;
            }

            if (!$issetRowAValue &&
                $rowA[$condition->getValue()] >= $condition->getColumn()[0] &&
                $rowA[$condition->getValue()] <= $condition->getColumn()[1]
            ) {
                return true;
            }
        }

        return false;
    }
}