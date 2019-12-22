<?php

/**
 * Class Optimizer
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
     * @var Query $query
     */
    private $query;

    /**
     * @var bool $useOrderBy
     */
    private $useOrderBy;

    /**
     * Optimizer constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
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
     * @param Condition $condition
     *
     * @return string
     */
    public function sayJoinAlgorithm(Condition $condition)
    {
        $equalOperator = $condition->getOperator() === Operator::EQUAL;

        if (count($this->query->getOrderBy()) === 1) {
            $orderBy = $this->query->getOrderBy()[0];

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
                self::NESTED_LOOP;
            }
        } elseif ($equalOperator) {
            return self::HASH_JOIN;
        } else {
            return self::NESTED_LOOP;
        }
    }

    /**
     * @param Table $innerJoinedTable
     * @return string
     */
    public function sayOrderOfInnerJoinedTables(Table $innerJoinedTable)
    {
        $countA = $this->query->getTable()->getRowsCount();
        $countB = $innerJoinedTable->getRowsCount();

        return $countA > $countB ? self::TABLE_B_FIRST : self::TABLE_A_FIRST;
    }

    /**
     * @return bool
     */
    public function sayIfOrderByIsNeed()
    {
        return $this->useOrderBy;
    }
}