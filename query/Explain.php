<?php

namespace query;

use Condition;
use Optimizer;
use Query;
use Row;
use Table;

/**
 * Class Explain
 *
 * @package query
 */
class Explain extends BaseQuery
{
    const JOIN_ALGORITHMS = [
        Optimizer::MERGE_JOIN => 'MERGE JOIN',
        Optimizer::HASH_JOIN =>'HASH JOIN',
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

        foreach ($query->getInnerJoin() as $innerJoinedTable) {
            $tables[] = new Row(
                [
                    'table' => $innerJoinedTable['table']->getName(),
                    'rows' => $innerJoinedTable['table']->getRowsCount(),
                    'type' => 'INNER JOIN',
                    'condition' => null,
                    'algorithm' => null,
                ]
            );

            /**
             * @var Condition $condition
             */
            foreach ($innerJoinedTable['onConditions'] as $condition) {
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

        /**
         * @var Table $crossJoinedTable
         */
        foreach ($query->getCrossJoin() as $crossJoinedTable) {
            $tables[] = new Row(
                [
                    'table' => $crossJoinedTable['table']->getName(),
                    'rows' => $crossJoinedTable['table']->getRowsCount(),
                    'type' => 'CROSS JOIN',
                    'condition' => null,
                    'algorithm' => self::JOIN_ALGORITHMS[Optimizer::NESTED_LOOP],
                ]
            );
        }

        /**
         * @var Table $leftJoinedTable
         */
        foreach ($query->getLeftJoin() as $leftJoinedTable) {
            $tables[] = new Row(
                [
                    'table' => $leftJoinedTable['table']->getName(),
                    'rows' => $leftJoinedTable['table']->getRowsCount(),
                    'type' => 'LEFT JOIN',
                    'condition' => null,
                    'algorithm' => null,
                ]
            );

            /**
             * @var Condition $condition
             */
            foreach ($leftJoinedTable['onConditions'] as $condition) {
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

        foreach ($query->getRightJoin() as $rightJoinedTable) {
            $tables[] = new Row(
                [
                    'table' => $rightJoinedTable['table']->getName(),
                    'rows' => $rightJoinedTable['table']->getRowsCount(),
                    'type' => 'RIGHT JOIN',
                    'condition' => null,
                    'algorithm' => null,
                ]
            );

            /**
             * @var Condition $condition
             */
            foreach ($rightJoinedTable['onConditions'] as $condition) {
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

        foreach ($query->getFullJoin() as $fullJoinedTable) {
            $tables[] = new Row(
                [
                    'table' => $fullJoinedTable['table']->getName(),
                    'rows' => $fullJoinedTable['table']->getRowsCount(),
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