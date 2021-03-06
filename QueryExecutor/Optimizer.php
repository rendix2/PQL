<?php

namespace pql\QueryExecutor;

use pql\Condition;
use pql\JoinedTable;
use pql\Operator;
use pql\QueryBuilder\Query;
use pql\QueryBuilder\SelectQuery as SelectBuilder;
use pql\Table;

/**
 * Class Optimizer
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class Optimizer
{
    /**
     * @var string
     */
    const MERGE_JOIN = 'merge';

    /**
     * @var string
     */
    const HASH_JOIN = 'hash';

    /**
     * @var string
     */
    const NESTED_LOOP = 'nested';

    /**
     * @var string
     */
    const TABLE_B_FIRST = 'b_first_a_bigger';

    /**
     * @var string
     */
    const TABLE_A_FIRST = 'a_first_b_bigger';

    /**
     * @var string
     */
    const CONDITION_VALUE_VALUE = 'value';

    /**
     * @var string
     */
    const CONDITION_COLUMN_VALUE = 'column';

    /**
     * @var SelectBuilder $query
     */
    private $query;

    /**
     * @var bool $useOrderBy
     */
    private $useOrderBy;

    /**
     * Optimizer constructor.
     *
     * @param SelectBuilder $query
     */
    public function __construct(SelectBuilder $query)
    {
        $this->query      = $query;
        $this->useOrderBy = $query->hasOrderBy();
    }

    /**
     * Optimizer Constructor
     */
    public function __destruct()
    {
        $this->query      = null;
        $this->useOrderBy = null;
    }

    /**
     * @param JoinedTable $joinedTable
     * @param Condition $condition
     *
     * @return string
     */
    public function sayJoinAlgorithm(JoinedTable $joinedTable, Condition $condition)
    {
        $equalOperator = $condition->getOperator() === Operator::EQUAL;

        if (count($this->query->getOrderByColumns()) === 1) {
            $orderBy = $this->query->getOrderByColumns()[0];

            if ($equalOperator &&
                (
                    $condition->getColumn() === $orderBy->getColumn() ||
                    $condition->getValue() === $orderBy->getColumn()
                )
            ) {
                $this->useOrderBy = false;

                return self::MERGE_JOIN;
            } elseif ($equalOperator) {
                return self::HASH_JOIN;
            } else {
                return self::NESTED_LOOP;
            }
        } elseif ($equalOperator) {
            if ($joinedTable->getTable() instanceof Query) {
                return self::NESTED_LOOP;
            } else {
                return self::HASH_JOIN;
            }
        } else {
            return self::NESTED_LOOP;
        }
    }

    /**
     * @param array $tableA
     * @param array $tableB
     *
     * @return string
     */
    public function sayOrderOfInnerJoinedTables(array $tableA, array $tableB)
    {
        return count($tableA) > count($tableB) ? self::TABLE_B_FIRST : self::TABLE_A_FIRST;
    }

    /**
     * @return bool
     */
    public function sayIfOrderByIsNeed()
    {
        return $this->useOrderBy;
    }

    /**
     * @param Condition $condition
     * @param Table     $fromTable
     * @param Table     $joinedTable
     *
     * @return bool|string
     */
    public function sayIfConditionContainsValue(Condition $condition, Table $fromTable, Table $joinedTable)
    {
        $column = $condition->getColumn();
        $value  = $condition->getValue();

        if (!$fromTable->columnExists($column) && !$joinedTable->columnExists($column)) {
            return self::CONDITION_COLUMN_VALUE;
        } elseif (!$fromTable->columnExists($value) && !$joinedTable->columnExists($value)) {
            return self::CONDITION_VALUE_VALUE;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function sayIfCanOptimizeWhere()
    {
        return count($this->query->getWhereConditions()) === 1;
    }
}
