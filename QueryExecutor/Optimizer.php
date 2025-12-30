<?php

namespace pql\QueryExecutor;

use pql\Condition;
use pql\JoinedTable;
use pql\Operator;
use pql\QueryBuilder\Query;
use pql\QueryBuilder\SelectQuery as SelectBuilder;
use pql\Table;
use pql\ITable;

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

    private const int SAFETY_RAM_THRESHOLD_MB = 50;

    private const int MEMORY_RESERVE_PER_ROW_BYTES = 1024;

    private SelectBuilder $query;

    private bool $useOrderBy;

    public function __construct(SelectBuilder $query)
    {
        $this->query      = $query;
        $this->useOrderBy = $query->hasOrderBy();
    }

    private function parseMemoryLimit(string $limitString): int
    {
        $limitString = trim($limitString);
        $last = strtolower($limitString[strlen($limitString) - 1]);
        $value = (int)$limitString;

        switch ($last) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }

        return ($limitString === '-1') ? PHP_INT_MAX : $value;
    }

    private function estimateMaterializedSize(ITable $table): int
    {
        $rowCount = $table->getRowsCount();

        return $rowCount * self::MEMORY_RESERVE_PER_ROW_BYTES;
    }

    private function getAvailableMemoryBytes(): int
    {
        $maxLimitBytes = $this->parseMemoryLimit(ini_get('memory_limit'));
        $usedMemoryBytes = memory_get_usage(true);

        $safetyBytes = self::SAFETY_RAM_THRESHOLD_MB * 1024 * 1024;

        $available = $maxLimitBytes - $usedMemoryBytes - $safetyBytes;

        return max(0, $available);
    }

    public function shouldMaterializeToDisk(ITable $table): bool
    {
        $estimatedSize = $this->estimateMaterializedSize($table);
        $availableRam = $this->getAvailableMemoryBytes();

        if ($estimatedSize > $availableRam) {
            return true;
        }

        return false;
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
