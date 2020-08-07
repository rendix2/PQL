<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 1. 2020
 * Time: 16:20
 */

namespace pql\QueryPrinter;

use pql\Condition;
use pql\JoinedTable;
use pql\QueryBuilder\Query;
use pql\QueryBuilder\SelectQuery as SelectBuilder;
use pql\Table;

/**
 * Class Select
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryPrinter
 * @internal
 */
class Select implements IQueryPrinter
{
    use Where;
    use Limit;
    use Offset;

    /**
     * @var SelectBuilder $query
     */
    private $query;

    /**
     * @var string $indent
     */
    private $indent;

    /**
     * @var int $level
     */
    private $level;

    /**
     * Select constructor.
     *
     * @param SelectBuilder $query
     * @param int           $level
     */
    public function __construct(SelectBuilder $query, $level = 0)
    {
        $this->query  = $query;
        $this->indent = $this->indent($level);
        $this->level  = $level;
    }

    private function indent($level)
    {
        return str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
    }

    /**
     * Select destructor.
     */
    public function __destruct()
    {
        $this->query = null;
    }

    /**
     * @inheritDoc
     */
    public function printQuery()
    {
        $select    = $this->columns();
        //$functions = $this->functions();

        $from = $this->from();

        $innerJoin = $this->innerJoin();
        $crossJoin = $this->crossJoin();

        $leftJoin  = $this->leftJoin();
        $rightJoin = $this->rightJoin();

        $fullJoin  = $this->fullJoin();

        $where = $this->where();

        $groupBy = $this->groupBy();
        $having  = $this->having();

        $orderBy = $this->orderBy();

        $limit = $this->limit();
        $offset = $this->offset();

        $union     = $this->union();
        $unionAll  = $this->unionAll();
        $intersect = $this->intersect();
        $except    = $this->except();

        $selectClause    = $select;
        $joins           = $innerJoin . $crossJoin . $leftJoin . $rightJoin . $fullJoin;
        $groupOperations = $groupBy . $having;
        $setOperations   = $union . $unionAll . $intersect . $except;
        $limitOperations = $limit . $offset;

        $query = $selectClause . $from . $joins . $where . $groupOperations . $orderBy . $limitOperations . $setOperations;;

        return $query;
    }

    /**
     * @return string
     */
    private function columns()
    {
        $select = '<br>' . $this->indent . 'SELECT ' . $this->distinct() . '<br>';

        foreach ($this->query->getSelectedColumns() as $i => $selectedColumn) {
            if ($i !== 0) {
                $select .= ',<br> ';
            }

            if ($selectedColumn->getExpression() instanceof \pql\QueryBuilder\Select\Query) {
                $select .= $this->indent . '(' . (string) $selectedColumn . '<br>)';
            } else {
                $select .= $this->indent . (string) $selectedColumn;
            }
        }

        return $select;
    }

    /**
     * @return string
     */
    private function distinct()
    {
        $distinct = '';

        if ($this->query->getDistinctColumn()) {
            $distinct = 'DISTINCT ';
        }

        return $distinct;
    }

    /**
     * @return string
     */
    private function from()
    {
        $from = '<br>' . $this->indent . 'FROM ';

        if ($this->query->getTable() instanceof Table) {
            $from .= $this->query->getTable()->getName();
        } elseif ($this->query->getTable() instanceof Query) {
            $queryPrinter = new QueryPrinter($this->query->getTable(), $this->level + 1);

            $from .= '(' . $queryPrinter->printQuery() . '<br>' . $this->indent . ')';
        }

        if ($this->query->hasTableAlias()) {
            $from .= ' AS ' . $this->query->getTableAlias()->getTo();
        }

        return $from;
    }

    /**
     * @return string
     */
    private function innerJoin()
    {
        $innerJoin = '';

        if ($this->query->hasInnerJoinedTable()) {
            foreach ($this->query->getInnerJoinedTables() as $innerJoinedTable) {
                $innerJoin .= '<br>' . $this->indent . 'INNER JOIN ';

                if ($innerJoinedTable->getTable() instanceof Table) {
                    $innerJoin .= $innerJoinedTable->getTable()->getName();
                } elseif ($innerJoinedTable->getTable() instanceof Query) {
                    $queryPrinter = new QueryPrinter($this->query->getTable(), $this->level + 1);

                    $innerJoin .= '(' . $this->indent . $queryPrinter->printQuery() . '<br>' . $this->indent . ')';
                }

                $innerJoin .= $this->printTableAlias($innerJoinedTable);
                $innerJoin .= $this->printOnConditions($innerJoinedTable->getOnConditions());
            }
        }
        
        return $innerJoin;
    }

    /**
     * @return string
     */
    private function crossJoin()
    {
        $crossJoin = '';

        if ($this->query->hasCrossJoinedTable()) {
            foreach ($this->query->getCrossJoinedTables() as $crossJoinedTable) {
                $crossJoin .= '<br>' . $this->indent . 'CROSS JOIN ';

                if ($crossJoinedTable->getTable() instanceof Table) {
                    $crossJoin .= $crossJoinedTable->getTable()->getName();
                } elseif ($crossJoinedTable->getTable() instanceof Query) {
                    $queryPrinter = new QueryPrinter($this->query->getTable(), $this->level + 1);

                    $crossJoin .= '(' . $this->indent . $queryPrinter->printQuery() . '<br>' . $this->indent . ')';
                }

                $crossJoin .= $this->printTableAlias($crossJoinedTable);
            }
        }
        
        return $crossJoin;
    }

    /**
     * @return string
     */
    private function leftJoin()
    {
        $leftJoin = '';

        if ($this->query->hasLeftJoinedTable()) {
            foreach ($this->query->getLeftJoinedTables() as $leftJoinedTable) {
                $leftJoin .= '<br>' . $this->indent . 'LEFT JOIN ';

                if ($leftJoinedTable->getTable() instanceof Table) {
                    $leftJoin .= $leftJoinedTable->getTable()->getName();
                } elseif ($leftJoinedTable->getTable() instanceof Query) {
                    $queryPrinter = new QueryPrinter($leftJoinedTable->getTable(), $this->level + 1);

                    $leftJoin .= '(' . $this->indent . $queryPrinter->printQuery() . '<br>' . $this->indent . ')';
                }

                $leftJoin .= $this->printTableAlias($leftJoinedTable);
                $leftJoin .= $this->printOnConditions($leftJoinedTable->getOnConditions());
            }
        }
        
        return $leftJoin;
    }

    /**
     * @return string
     */
    private function rightJoin()
    {
        $rightJoin = '';

        if ($this->query->hasRightJoinedTable()) {
            foreach ($this->query->getRightJoinedTables() as $rightJoinedTable) {
                $rightJoin .= '<br>' . $this->indent . 'RIGHT JOIN ';

                if ($rightJoinedTable->getTable() instanceof Table) {
                    $rightJoin .= $rightJoinedTable->getTable()->getName();
                } elseif ($rightJoinedTable->getTable() instanceof Query) {
                    $queryPrinter = new QueryPrinter($rightJoinedTable->getTable(), $this->level + 1);

                    $rightJoin .= '(' . $this->indent . $queryPrinter->printQuery() . '<br>' . $this->indent . ')';
                }

                $rightJoin .= $this->printTableAlias($rightJoinedTable);
                $rightJoin .= $this->printOnConditions($rightJoinedTable->getOnConditions());
            }
        }
        
        return $rightJoin;
    }

    /**
     * @return string
     */
    private function fullJoin()
    {
        $fullJoin = '';

        if ($this->query->hasFullJoinedTable()) {
            foreach ($this->query->getFullJoinedTables() as $fullJoinedTable) {
                $fullJoin .= '<br>' . $this->indent . 'FULL JOIN ';

                if ($fullJoinedTable->getTable() instanceof Table) {
                    $fullJoin .= $fullJoinedTable->getTable()->getName();
                } elseif ($fullJoinedTable->getTable() instanceof Query) {
                    $queryPrinter = new QueryPrinter($fullJoinedTable->getTable(), $this->level + 1);

                    $fullJoin .= '(' . $this->indent . $queryPrinter->printQuery() . '<br>' . $this->indent . ')';
                }

                $fullJoin .= $this->printTableAlias($fullJoinedTable);
                $fullJoin .= $this->printOnConditions($fullJoinedTable->getOnConditions());
            }
        }
        
        return $fullJoin;
    }

    /**
     * @return string
     */
    private function groupBy()
    {
        $groupBy = '';

        if ($this->query->hasGroupBy()) {
            $groupBy = '<br>' . $this->indent . 'GROUP BY ';

            foreach ($this->query->getGroupByColumns() as $i => $groupByColumn) {
                if ($i !== 0) {
                    $groupBy .= ', ';
                }

                $groupBy .= (string) $groupByColumn;
            }
        }
        
        return $groupBy;
    }

    /**
     * @return string
     */
    private function having()
    {
        $having = '';

        if ($this->query->hasHavingCondition()) {
            $having = '<br>' . $this->indent . 'HAVING ';

            foreach ($this->query->getHavingConditions() as $i => $havingCondition) {
                if ($i !== 0) {
                    $having .= ' <br> ' . $this->indent . 'AND ';
                }

                $having.= (string) $havingCondition;
            }
        }
        
        return $having;
    }

    /**
     * @return string
     */
    private function orderBy()
    {
        $orderBy = '';

        if ($this->query->hasOrderBy()) {
            $orderBy = '<br>' . $this->indent . 'ORDER BY ';

            foreach ($this->query->getOrderByColumns() as $i => $orderedBy) {
                if ($i !== 0) {
                    $orderBy .= ', ';
                }

                $orderBy .= (string) $orderedBy;
            }
        }
        
        return $orderBy;
    }

    /**
     * @return string
     */
    private function union()
    {
        $union = '';

        foreach ($this->query->getUnionQueries() as $i => $unionQuery) {
            if ($i === 0) {
                $union .= '<br><br>';
            }

            $queryPrinter = new QueryPrinter($unionQuery, $this->level);

            $union .= $this->indent . 'UNION <br><br>' . $queryPrinter->printQuery();
        }

        return $union;
    }

    /**
     * @return string
     */
    private function unionAll()
    {
        $unionAll = '';

        foreach ($this->query->getUnionAllQueries() as $i => $unionAllQuery) {
            if ($i === 0) {
                $unionAll .= '<br><br>';
            }

            $queryPrinter = new QueryPrinter($unionAllQuery, $this->level);

            $unionAll .= $this->indent . 'UNION ALL <br><br>' .$queryPrinter->printQuery();
        }

        return $unionAll;
    }

    /**
     * @return string
     */
    private function intersect()
    {
        $intersect = '';

        foreach ($this->query->getIntersectQueries() as $i => $intersectQuery) {
            if ($i === 0) {
                $intersect .= '<br><br>';
            }

            $queryPrinter = new QueryPrinter($intersectQuery, $this->level);

            $intersect .= $this->indent . 'INTERSECT <br><br>' . $queryPrinter->printQuery();
        }

        return $intersect;
    }

    /**
     * @return string
     */
    private function except()
    {
        $except = '';

        foreach ($this->query->getExceptQueries() as $i => $exceptQuery) {
            if ($i === 0) {
                $except .= '<br><br>';
            }

            $queryPrinter = new QueryPrinter($exceptQuery, $this->level);

            $except .= $this->indent . 'EXCEPT <br><br>' . $queryPrinter->printQuery();
        }

        return $except;
    }

    /**
     * @param JoinedTable $table
     *
     * @return string
     */
    private function printTableAlias(JoinedTable $table)
    {
        $tableAlias = '';

        if ($table->hasAlias()) {
            $tableAlias = ' AS ' . $table->getAlias()->getTo();
        }

        return $tableAlias;
    }

    /**
     * @param array $conditions
     *
     * @return string
     */
    private function printOnConditions(array $conditions)
    {
        $onCondition = '';

        /**
         * @var Condition $condition
         */
        foreach ($conditions as $i => $condition) {
            if ($i === 0) {
                $onCondition .= '<br> ' . $this->indent . 'ON ' . (string)$condition;
            } else {
                $onCondition .= '<br> ' . $this->indent . 'AND '. (string) $condition;
            }
        }

        return $onCondition;
    }
}
