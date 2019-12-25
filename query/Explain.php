<?php

namespace query;

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

        if ($query->getTable() instanceof Table) {
            $row = [
                'table' => $query->getTable()->getName(),
                'rows' => $query->getTable()->getRowsCount(),
                'type' => 'FROM CLAUSE',
                'condition' => null,
                'algorithm' => null,
            ];

            $tables[] = new Row($row);
        } elseif ($query->getTable() instanceof Query) {
            $row = [
                'table' => 'sub query',
                'rows' => '---',
                'type' => 'FROM CLAUSE',
                'condition' => null,
                'algorithm' => null,
            ];

            $tables[] = new Row($row);
            $tables = array_merge($tables, $this->explainHelper($query->getTable()));
        }

        foreach ($query->getInnerJoinedTables() as $innerJoinedTable) {
            if ($innerJoinedTable->getTable() instanceof Table) {
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
            } elseif ($innerJoinedTable->getTable() instanceof Query) {
                $row = [
                    'table' => 'sub query',
                    'rows' => '---',
                    'type' => 'INNER JOIN',
                    'condition' => null,
                    'algorithm' => null,
                ];

                $tables[] = new Row($row);
                $tables = array_merge($tables, $this->explainHelper($innerJoinedTable->getTable()));
            }
        }

        foreach ($query->getCrossJoinedTables() as $crossJoinedTable) {
            if ($crossJoinedTable->getTable() instanceof Table) {
                $tables[] = new Row(
                    [
                        'table' => $crossJoinedTable->getTable()->getName(),
                        'rows' => $crossJoinedTable->getTable()->getRowsCount(),
                        'type' => 'CROSS JOIN',
                        'condition' => null,
                        'algorithm' => self::JOIN_ALGORITHMS[Optimizer::NESTED_LOOP],
                    ]
                );
            } elseif ($crossJoinedTable->getTable() instanceof Query) {
                $row = [
                    'table' => 'sub query',
                    'rows' => '---',
                    'type' => 'CROSS JOIN',
                    'condition' => null,
                    'algorithm' => null,
                ];

                $tables[] = new Row($row);
                $tables = array_merge($tables, $this->explainHelper($crossJoinedTable->getTable()));
            }
        }

        foreach ($query->getLeftJoinedTables() as $leftJoinedTable) {
            if ($leftJoinedTable->getTable() instanceof Table) {
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
            } elseif ($leftJoinedTable->getTable() instanceof Query) {
                $row = [
                    'table' => 'sub query',
                    'rows' => '---',
                    'type' => 'LEFT JOIN',
                    'condition' => null,
                    'algorithm' => null,
                ];

                $tables[] = new Row($row);
                $tables = array_merge($tables, $this->explainHelper($leftJoinedTable->getTable()));
            }
        }

        foreach ($query->getRightJoinedTables() as $rightJoinedTable) {
            if ($rightJoinedTable->getTable() instanceof Table) {
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
            } elseif ($rightJoinedTable->getTable() instanceof Query) {
                $row = [
                    'table' => 'sub query',
                    'rows' => '---',
                    'type' => 'RIGHT JOIN',
                    'condition' => null,
                    'algorithm' => null,
                ];

                $tables[] = new Row($row);
                $tables = array_merge($tables, $this->explainHelper($rightJoinedTable->getTable()));
            }
        }

        foreach ($query->getFullJoinedTables() as $fullJoinedTable) {
            if ($fullJoinedTable instanceof Table) {

                $tables[] = new Row(
                    [
                        'table' => $fullJoinedTable->getTable()->getName(),
                        'rows' => $fullJoinedTable->getTable()->getRowsCount(),
                        'type' => 'FULL JOIN',
                        'condition' => null,
                        'algorithm' => null,
                    ]
                );

                foreach ($fullJoinedTable->getOnConditions() as $condition) {
                    $tables[] = new Row(
                        [
                            'table' => '---',
                            'rows' => '---',
                            'type' => '---',
                            'condition' => (string)$condition,
                            'algorithm' => self::JOIN_ALGORITHMS[$this->optimizer->sayJoinAlgorithm($condition)]
                        ]
                    );
                }
            } elseif ($fullJoinedTable->getTable() instanceof Query) {
                $row = [
                    'table' => 'sub query',
                    'rows' => '---',
                    'type' => 'FULL JOIN',
                    'condition' => null,
                    'algorithm' => null,
                ];

                $tables[] = new Row($row);
                $tables = array_merge($tables, $this->explainHelper($fullJoinedTable->getTable()));
            }
        }

        return $tables;
    }


    /**
     * @inheritDoc
     */
    public function run()
    {
        $tables = $this->explainHelper($this->query);

        foreach ($this->query->getUnionQueries() as $i => $unionQuery) {
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

        foreach ($this->query->getUnionAllQueries() as $i => $unionAllQuery) {
            $result = $this->explainHelper($unionAllQuery);
            $tables[] = new Row(
                [
                    'table' => '',
                    'rows' => '',
                    'type' => 'UNION ALL #' . $i,
                    'condition' => '',
                    'algorithm' => '',
                ]
            );

            $tables = array_merge($tables, $result);
        }

        foreach ($this->query->getIntersectQueries() as $i => $intersectQuery) {
            $result = $this->explainHelper($intersectQuery);
            $tables[] = new Row(
                [
                    'table' => '',
                    'rows' => '',
                    'type' => 'INTERSECT #' . $i,
                    'condition' => '',
                    'algorithm' => '',
                ]
            );

            $tables = array_merge($tables, $result);
        }

        foreach ($this->query->getExceptQueries() as $i => $exceptQuery) {
            $result = $this->explainHelper($exceptQuery);
            $tables[] = new Row(
                [
                    'table' => '',
                    'rows' => '',
                    'type' => 'EXCEPT #' . $i,
                    'condition' => '',
                    'algorithm' => '',
                ]
            );

            $tables = array_merge($tables, $result);
        }

        return $this->result = $tables;
    }
}