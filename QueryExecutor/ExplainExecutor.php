<?php

namespace pql\QueryExecutor;

use pql\JoinedTable;
use pql\QueryBuilder\ExplainQuery as ExplainBuilder;
use pql\QueryBuilder\IQueryBuilder;
use pql\QueryBuilder\Query;
use pql\QueryRow\ExplainRow;
use pql\Table;

/**
 * Class Explain
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class ExplainExecutor implements IQueryExecutor
{
    /**
     * @var array
     */
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
     * @var ExplainBuilder $query
     */
    private $query;

    /**
     * @var array $result
     */
    private $result;

    /**
     * Explain constructor.
     *
     * @param IQueryBuilder $query
     */
    public function __construct(IQueryBuilder $query)
    {
        $this->query     = $query;
        $this->optimizer = new Optimizer($query);
    }

    /**
     * Explain destructor.
     */
    public function __destruct()
    {
        $this->optimizer = null;
        $this->query     = null;
    }

    /**
     * @return ExplainBuilder
     */
    private function getQuery()
    {
        return $this->query;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function columns(IQueryBuilder $query)
    {
        $tables = [];

        foreach ($query->getSelectedColumns() as $selectedColumn) {
            if ($selectedColumn instanceof Query) {
                $explain = new ExplainExecutor($selectedColumn);

                $tables[] = new ExplainRow(
                    'SELECTED COLUMN',
                    '---',
                    'SUB QUERY',
                    null,
                    null,
                    $explain->run()
                );
            }
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function from(IQueryBuilder $query)
    {
        $tables = [];

        if ($query->getTable() instanceof Table) {
            $tables[] = new ExplainRow(
                $query->getTable()->getName(),
                $query->getTable()->getRowsCount(),
                'FROM CLAUSE',
                null,
                null,
                null
            );
        } elseif ($query->getTable() instanceof Query) {
            $explain = new ExplainExecutor($query->getTable()->getQuery());

            $tables[] = new ExplainRow(
                'FROM CLAUSE',
                '---',
                'SUB QUERY',
                null,
                null,
                $explain->run()
            );
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function innerJoin(IQueryBuilder $query)
    {
        $tables = [];

        foreach ($query->getInnerJoinedTables() as $innerJoinedTable) {
            if ($innerJoinedTable->getTable() instanceof Table) {
                $tables[] = new ExplainRow(
                    $innerJoinedTable->getTable()->getName(),
                    $innerJoinedTable->getTable()->getRowsCount(),
                    'INNER JOIN',
                    null,
                    null,
                    null
                );

                $tables = array_merge($tables, $this->onConditions($innerJoinedTable));
            } elseif ($innerJoinedTable->getTable() instanceof Query) {
                $innerJoinQuery = $innerJoinedTable->getTable()->getQuery();

                $explain = new ExplainExecutor($innerJoinQuery);

                $tables[] = new ExplainRow(
                    $innerJoinQuery->getTable()->getName(),
                    $innerJoinQuery->getTable()->getRowsCount(),
                    'INNER JOIN',
                    null,
                    null,
                    $explain->run()
                );
            }
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function crossJoin(IQueryBuilder $query)
    {
        $tables = [];

        foreach ($query->getCrossJoinedTables() as $crossJoinedTable) {
            if ($crossJoinedTable->getTable() instanceof Table) {
                $tables[] = new ExplainRow(
                    $crossJoinedTable->getTable()->getName(),
                    $crossJoinedTable->getTable()->getRowsCount(),
                    'CROSS JOIN',
                    null,
                    null,
                    null
                );
            } elseif ($crossJoinedTable->getTable() instanceof Query) {
                $crossJoinQuery = $crossJoinedTable->getTable()->getQuery();

                $explain = new ExplainExecutor($crossJoinQuery);

                $tables[] = new ExplainRow(
                    $crossJoinQuery->getTable()->getName(),
                    $crossJoinQuery->getTable()->getRowsCount(),
                    'CROSS JOIN',
                    null,
                    null,
                    $explain->run()
                );
            }
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function leftJoin(IQueryBuilder $query)
    {
        $tables = [];

        foreach ($query->getLeftJoinedTables() as $leftJoinedTable) {
            if ($leftJoinedTable->getTable() instanceof Table) {
                $tables[] = new ExplainRow(
                    $leftJoinedTable->getTable()->getName(),
                    $leftJoinedTable->getTable()->getRowsCount(),
                    'LEFT JOIN',
                    null,
                    null,
                    null
                );

                $tables = array_merge($tables, $this->onConditions($leftJoinedTable));
            } elseif ($leftJoinedTable->getTable() instanceof Query) {
                $leftJoinQuery = $leftJoinedTable->getTable()->getQuery();

                $explain = new ExplainExecutor($leftJoinedTable->getTable()->getQuery());

                $tables[] = new ExplainRow(
                    $leftJoinQuery->getTable()->getName(),
                    $leftJoinQuery->getTable()->getRowsCount(),
                    'LEFT JOIN',
                    null,
                    null,
                    $explain->run()
                );
            }
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function rightJoin(IQueryBuilder $query)
    {
        $tables = [];

        foreach ($query->getRightJoinedTables() as $rightJoinedTable) {
            if ($rightJoinedTable->getTable() instanceof Table) {
                $tables[] = new ExplainRow(
                    $rightJoinedTable->getTable()->getName(),
                    $rightJoinedTable->getTable()->getRowsCount(),
                    'RIGHT JOIN',
                    null,
                    null,
                    null
                );

                $tables = array_merge($tables, $this->onConditions($rightJoinedTable));
            } elseif ($rightJoinedTable->getTable() instanceof Query) {
                $rightJoinQuery = $rightJoinedTable->getTable()->getQuery();

                $explain = new ExplainExecutor($rightJoinQuery);

                $tables[] = new ExplainRow(
                    $rightJoinQuery->getTable()->getName(),
                    $rightJoinQuery->getTable()->getRowsCount(),
                    'RIGHT JOIN',
                    null,
                    null,
                    $explain->run()
                );
            }
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function fullJoin(IQueryBuilder $query)
    {
        $tables = [];

        foreach ($query->getFullJoinedTables() as $fullJoinedTable) {
            if ($fullJoinedTable instanceof Table) {
                $tables[] = new ExplainRow(
                    $fullJoinedTable->getTable()->getName(),
                    $fullJoinedTable->getTable()->getRowsCount(),
                    'FULL JOIN',
                    null,
                    null,
                    null
                );

                $tables = array_merge($tables, $this->onConditions($fullJoinedTable));
            } elseif ($fullJoinedTable->getTable() instanceof Query) {
                $fullJoinQuery = $fullJoinedTable->getTable()->getQuery();

                $explain = new ExplainExecutor($fullJoinQuery);

                $tables[] = new ExplainRow(
                    $fullJoinQuery->getTable()->getName(),
                    $fullJoinQuery->getTable()->getRowsCount(),
                    'FULL JOIN',
                    null,
                    null,
                    $explain->run()
                );
            }
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function where(IQueryBuilder $query)
    {
        $tables = [];

        foreach ($query->getWhereConditions() as $whereCondition) {
            if ($whereCondition->getColumn() instanceof Query) {
                $explain = new ExplainExecutor($whereCondition->getColumn());
                $tables[] = $explain->run();
            }

            if ($whereCondition->getValue() instanceof Query) {
                $explain = new ExplainExecutor($whereCondition->getValue());
                $tables[] = $explain->run();
            }
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function having(IQueryBuilder $query)
    {
        $tables = [];

        foreach ($query->getHavingConditions() as $havingCondition) {
            if ($havingCondition->getValue() instanceof Query) {
                $explain = new ExplainExecutor($havingCondition->getValue());
                $tables[] = $explain->run();
            }

            if ($havingCondition->getColumn() instanceof Query) {
                $explain = new ExplainExecutor($havingCondition->getColumn());
                $tables[] = $explain->run();
            }
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function union(IQueryBuilder $query)
    {
        $tables = [];

        foreach ($query->getUnionQueries() as $i => $unionQuery) {
            $explain = new ExplainExecutor($unionQuery);

            $tables[] = new ExplainRow(
                '',
                '',
                'UNION #' . $i,
                null,
                null,
                $explain->run()
            );
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function unionAll(IQueryBuilder $query)
    {
        $tables = [];

        foreach ($query->getUnionAllQueries() as $i => $unionAllQuery) {
            $explain = new ExplainExecutor($unionAllQuery);

            $tables[] = new ExplainRow(
                '',
                '',
                'UNION ALL #' . $i,
                null,
                null,
                $explain->run()
            );
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function except(IQueryBuilder $query)
    {
        $tables = [];

        foreach ($query->getExceptQueries() as $i => $exceptQuery) {
            $explain = new ExplainExecutor($exceptQuery);

            $tables[] = new ExplainRow(
                '',
                '',
                'EXCEPT #' . $i,
                null,
                null,
                $explain->run()
            );
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function intersect(IQueryBuilder $query)
    {
        $tables = [];

        foreach ($query->getIntersectQueries() as $i => $intersectQuery) {
            $explain = new ExplainExecutor($intersectQuery);

            $tables[] = new ExplainRow(
                '',
                '',
                'INTERSECT #' . $i,
                null,
                null,
                $explain->run()
            );
        }

        return $tables;
    }

    /**
     * @param IQueryBuilder $query
     *
     * @return ExplainRow[]
     */
    private function explainHelper(IQueryBuilder $query)
    {
        return array_merge(
            $this->columns($query),
            $this->from($query),
            $this->innerJoin($query),
            $this->crossJoin($query),
            $this->leftJoin($query),
            $this->rightJoin($query),
            $this->fullJoin($query),
            $this->where($query),
            $this->having($query)
        );
    }

    /**
     * @param JoinedTable $joinedTable
     *
     * @return ExplainRow[]
     */
    private function onConditions(JoinedTable $joinedTable)
    {
        $tables = [];

        foreach ($joinedTable->getOnConditions() as $condition) {
            $tables[] = new ExplainRow(
                '---',
                '---',
                '---',
                (string)$condition,
                self::JOIN_ALGORITHMS[$this->optimizer->sayJoinAlgorithm($joinedTable, $condition)],
                null
            );
        }

        return $tables;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $tables = array_merge(
            $this->explainHelper($this->query),
            $this->union($this->query),
            $this->unionAll($this->query),
            $this->intersect($this->query),
            $this->except($this->query)
        );

        return $this->result = $tables;
    }
}
