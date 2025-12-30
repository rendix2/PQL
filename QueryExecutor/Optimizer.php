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
    public const string MERGE_JOIN = 'merge';

    public const string HASH_JOIN = 'hash';

    public const string NESTED_LOOP = 'nested';

    public const string TABLE_B_FIRST = 'b_first_a_bigger';

    public const string TABLE_A_FIRST = 'a_first_b_bigger';

    public const string CONDITION_VALUE_VALUE = 'value';

    public const string CONDITION_COLUMN_VALUE = 'column';

    private SelectBuilder $query;

    private bool $useOrderBy;

    public function __construct(SelectBuilder $query)
    {
        $this->query      = $query;
        $this->useOrderBy = $query->hasOrderBy();
    }

    /**
     * @param JoinedTable $joinedTable
     * @param Condition $condition
     *
     * @return string
     */
    public function sayJoinAlgorithm(JoinedTable $joinedTable, Condition $condition): string
    {
        $equalOperator = $condition->getOperator()->evaluate() === Operator::EQUAL;

        if (count($this->query->getOrderByColumns()) === 1) {
            $orderBy = $this->query->getOrderByColumns()[0];

            if ($equalOperator &&
                (
                    $condition->getColumn()->evaluate() === $orderBy->getColumn() ||
                    $condition->getValue()->evaluate() === $orderBy->getColumn()
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

    public function sayOrderOfInnerJoinedTables(array $tableA, array $tableB): string
    {
        return count($tableA) > count($tableB) ? self::TABLE_B_FIRST : self::TABLE_A_FIRST;
    }

    public function sayIfOrderByIsNeed(): bool
    {
        return $this->useOrderBy;
    }

    public function sayIfConditionContainsValue(Condition $condition, Table $fromTable, Table $joinedTable): string|false
    {
        $column = $condition->getColumn()->evaluate();
        $value  = $condition->getValue()->evaluate();

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
    public function sayIfCanOptimizeWhere(): bool
    {
        return count($this->query->getWhereConditions()) === 1;
    }
}
