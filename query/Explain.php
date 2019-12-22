<?php

namespace query;

use Optimizer;
use Query;
use Row;

/**
 * Class Explain
 *
 * @package query
 */
class Explain extends BaseQuery
{
    const JOIN_ALGORITHMS = [
        Optimizer::MERGE_JOIN  => 'MERGE JOIN',
        Optimizer::HASH_JOIN   => 'HASH JOIN',
        Optimizer::NESTED_LOOP => 'NESTED LOOP',
    ];

    /**
     * @var Optimizer $optimizer
     */
    private $optimizer;

    /**
     * Explain constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        parent::__construct($query);

        $this->optimizer = new Optimizer($query);
    }

    /**
     * Explain destructor.
     */
    public function __destruct()
    {
        $this->optimizer = null;

        parent::__destruct();
    }

    /**
     * @param Query $query
     *
     * @return array
     */
    private function explainHelper(Query $query)
    {
        $tables = [];

        $row = [
            'table' => $query->getTable()->getName(),
            'rows' => $query->getTable()->getRowsCount(),
            'type' => 'FROM CLAUSE',
            'condition' => null,
            'algorithm' => null,
        ];

        $row = new Row($row);

        $tables[] = $row;

        foreach ($query->getInnerJoinedTables() as $innerJoinedTable) {
            $tables[] = new Row(
                [
                    'table' => $innerJoinedTable->getTable()->getName(),
                    'rows' => $innerJoinedTable->getTable()->getRowsCount(),
                    'type' => 'INNER JOIN',
                    'condition' => null,
                    'algorithm' => null,
                ]
            );

            foreach ($innerJoinedTable->getOnConditions() as $condition) {
                $tables[] = new Row(
                    [
                        'table' => '---',
                        'rows' => '---',
                        'type' => '---',
                        'condition' => (string) $condition,
                        'algorithm' => self::JOIN_ALGORITHMS[$this->optimizer->sayJoinAlgorithm($condition)]
                    ]
                );
            }
        }

        foreach ($query->getCrossJoinedTables() as $crossJoinedTable) {
            $tables[] = new Row(
                [
                    'table' => $crossJoinedTable->getTable()->getName(),
                    'rows' => $crossJoinedTable->getTable()->getRowsCount(),
                    'type' => 'CROSS JOIN',
                    'condition' => null,
                    'algorithm' => self::JOIN_ALGORITHMS[Optimizer::NESTED_LOOP],
                ]
            );
        }

        foreach ($query->getLeftJoinedTables() as $leftJoinedTable) {
            $tables[] = new Row(
                [
                    'table' => $leftJoinedTable->getTable()->getName(),
                    'rows' => $leftJoinedTable->getTable()->getRowsCount(),
                    'type' => 'LEFT JOIN',
                    'condition' => null,
                    'algorithm' => null,
                ]
            );

            foreach ($leftJoinedTable->getOnConditions() as $condition) {
                $tables[] = new Row(
                    [
                        'table' => '---',
                        'rows' => '---',
                        'type' => '---',
                        'condition' => (string) $condition,
                        'algorithm' => self::JOIN_ALGORITHMS[$this->optimizer->sayJoinAlgorithm($condition)]
                    ]
                );
            }
        }

        foreach ($query->getRightJoinedTables() as $rightJoinedTable) {
            $tables[] = new Row(
                [
                    'table' => $rightJoinedTable->getTable()->getName(),
                    'rows' => $rightJoinedTable->getTable()->getRowsCount(),
                    'type' => 'RIGHT JOIN',
                    'condition' => null,
                    'algorithm' => null,
                ]
            );

            foreach ($rightJoinedTable->getOnConditions() as $condition) {
                $tables[] = new Row(
                    [
                        'table' => '---',
                        'rows' => '---',
                        'type' => '---',
                        'condition' => (string) $condition,
                        'algorithm' => self::JOIN_ALGORITHMS[$this->optimizer->sayJoinAlgorithm($condition)]
                    ]
                );
            }
        }

        foreach ($query->getFullJoinedTables() as $fullJoinedTable) {
            $tables[] = new Row(
                [
                    'table' => $fullJoinedTable->getTable()->getName(),
                    'rows' => $fullJoinedTable->getTable()->getRowsCount(),
                    'type' => 'FULL JOIN',
                    'condition' => null,
                    'algorithm' => null,
                ]
            );
        }

        return $tables;
    }


    /**
     * @inheritDoc
     */
    public function run()
    {
        $tables = $this->explainHelper($this->query);

        foreach ($this->query->getUnion() as $i => $unionQuery) {
            $result = $this->explainHelper($unionQuery);
            $tables[] = new Row(
                [
                    'table' => '',
                    'rows' => '',
                    'type' => 'UNION #' . $i,
                    'condition' => '',
                    'algorithm' => '',
                ]
            );

            $tables = array_merge($tables, $result);
        }

        return $this->result = $tables;
    }
}