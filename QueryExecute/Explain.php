<?php

namespace pql\QueryExecute;

use pql\ExplainRow;
use pql\JoinedTable;
use pql\Optimizer;
use pql\Query;
use pql\Table;

/**
 * Class Explain
 *
 * @author rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class Explain extends BaseQuery
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
     * @return ExplainRow[]
     */
    private function columns(Query $query)
    {
        $tables = [];

        foreach ($query->getSelectedColumns() as $selectedColumn) {
            if ($selectedColumn instanceof Query) {
                $explain = new Explain($selectedColumn);

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
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function from(Query $query)
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
            $explain = new Explain($query->getTable());

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
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function innerJoin(Query $query)
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
                $explain = new Explain($innerJoinedTable->getTable());

                $tables[] = new ExplainRow(
                    $innerJoinedTable->getTable()->getName(),
                    $innerJoinedTable->getTable()->getRowsCount(),
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
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function crossJoin(Query $query)
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
                $explain = new Explain($crossJoinedTable->getTable());

                $tables[] = new ExplainRow(
                    $crossJoinedTable->getTable()->getName(),
                    $crossJoinedTable->getTable()->getRowsCount(),
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
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function leftJoin(Query $query)
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
                $explain = new Explain($leftJoinedTable->getTable());

                $tables[] = new ExplainRow(
                    $leftJoinedTable->getTable()->getName(),
                    $leftJoinedTable->getTable()->getRowsCount(),
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
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function rightJoin(Query $query)
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
                $explain = new Explain($rightJoinedTable->getTable());

                $tables[] = new ExplainRow(
                    $rightJoinedTable->getTable()->getName(),
                    $rightJoinedTable->getTable()->getRowsCount(),
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
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function fullJoin(Query $query)
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
                $explain = new Explain($fullJoinedTable->getTable());

                $tables[] = new ExplainRow(
                    $fullJoinedTable->getTable()->getName(),
                    $fullJoinedTable->getTable()->getRowsCount(),
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
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function where(Query $query)
    {
        $tables = [];

        foreach ($query->getWhereConditions() as $whereCondition) {
            if ($whereCondition->getColumn() instanceof Query) {
                $explain = new Explain($whereCondition->getColumn());
                $tables[] = $explain->run();
            }

            if ($whereCondition->getValue() instanceof Query) {
                $explain = new Explain($whereCondition->getValue());
                $tables[] = $explain->run();
            }
        }

        return $tables;
    }

    /**
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function having(Query $query)
    {
        $tables = [];

        foreach ($query->getHavingConditions() as $havingCondition) {
            if ($havingCondition->getValue() instanceof Query) {
                $explain = new Explain($havingCondition->getValue());
                $tables[] = $explain->run();
            }

            if ($havingCondition->getColumn() instanceof Query) {
                $explain = new Explain($havingCondition->getColumn());
                $tables[] = $explain->run();
            }
        }

        return $tables;
    }

    /**
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function union(Query $query)
    {
        $tables = [];

        foreach ($query->getUnionQueries() as $i => $unionQuery) {
            $explain = new Explain($unionQuery);

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
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function unionAll(Query $query)
    {
        $tables = [];

        foreach ($query->getUnionAllQueries() as $i => $unionAllQuery) {
            $explain = new Explain($unionAllQuery);

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
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function except(Query $query)
    {
        $tables = [];

        foreach ($query->getExceptQueries() as $i => $exceptQuery) {
            $explain = new Explain($exceptQuery);

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
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function intersect(Query $query)
    {
        $tables = [];

        foreach ($query->getIntersectQueries() as $i => $intersectQuery) {
            $explain = new Explain($intersectQuery);

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
     * @param Query $query
     *
     * @return ExplainRow[]
     */
    private function explainHelper(Query $query)
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
                self::JOIN_ALGORITHMS[$this->optimizer->sayJoinAlgorithm($condition)],
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
