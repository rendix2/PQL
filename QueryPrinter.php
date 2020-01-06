<?php

/**
 * Class QueryPrinter
 */
class QueryPrinter
{
    /**
     * @var Query $query
     */
    private $query;

    /**
     * QueryPrinter constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * QueryPrinter destructor.
     */
    public function __destruct()
    {
        $this->query = null;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function printQuery()
    {
        switch ($this->query->getType()) {
            case Query::SELECT:
                return $this->select();
            case Query::INSERT:
                return $this->insert();
            case Query::DELETE:
                return $this->delete();
            case Query::UPDATE:
                return $this->update();
            case Query::EXPLAIN:
                return $this->explain();
            case Query::INSERT_SELECT:
                return $this->insertSelect();
            default:
                return 'Unknown query type.';
        }
    }

    /**
     * @return string
     */
    private function printWhere()
    {
        $whereCount = count($this->query->getWhereConditions());
        $where = '';

        if ($whereCount) {
            $where = ' <br>WHERE ';

            --$whereCount;

            foreach ($this->query->getWhereConditions() as $i => $whereCondition) {
                if ($whereCondition->getValue() instanceof Query) {
                    $value = '(' . (string)$whereCondition->getValue() . ')';
                } elseif (is_array($whereCondition->getValue())) {
                    $value =  '(' . implode(',', $whereCondition->getValue()) . ')';
                } else {
                    $value = $whereCondition->getValue();
                }

                if ($whereCondition->getColumn() instanceof Query) {
                    $column = '(' . (string)$whereCondition->getColumn() . ')';
                } elseif (is_array($whereCondition->getColumn())) {
                    $column = implode(',', $whereCondition->getColumn());
                } else {
                    $column = $whereCondition->getColumn();
                }

                $where .= ' ' . $column . ' ' . mb_strtoupper($whereCondition->getOperator()) . ' ' . $value;

                if ($whereCount !== $i) {
                    $where .= ' <br> &nbsp;&nbsp;&nbsp;&nbsp;AND';
                }
            }
        }

        return $where;
    }

    /**
     * @return string
     */
    private function printLimit()
    {
        $limit = '';

        if ($this->query->getLimit()) {
            $limit = '<br> LIMIT ' . $this->query->getLimit();
        }

        return $limit;
    }

    /**
     * @return string
     */
    private function printOffset()
    {
        $offset = '';

        if ($this->query->getOffset() !== 0) {
            $offset = '<br> OFFSET ' . $this->query->getOffset();
        }

        return $offset;
    }

    /**
     * @param array $conditions
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
                $onCondition .= ' <br> &nbsp;&nbsp;&nbsp;&nbsp;ON '. (string) $condition;
            } else {
                $onCondition .= ' <br> &nbsp;&nbsp;&nbsp;&nbsp;AND '. (string) $condition;
            }
        }

        return $onCondition;
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
     * @return string
     */
    private function select()
    {
        $select = 'SELECT ';

        foreach ($this->query->getSelectedColumns() as $i => $selectedColumn) {
            if ($i !== 0) {
                $select .= ', ';
            }

            $select .= (string) $selectedColumn;
        }

        $functions = '';

        $columnsCount = $this->query->getSelectedColumnsCount();

        foreach ($this->query->getFunctions() as $i => $function) {
            if (($i === 0 && $columnsCount) || $i > 0) {
                $functions .= ', ' . mb_strtoupper($function->getName()) . '(' . implode(', ', $function->getParams()) . ')';
            } elseif ($i === 0 && !$columnsCount) {
                $functions .= mb_strtoupper($function->getName()) . '(' . implode(', ', $function->getParams()) . ')';
            }
        }

        $from = '<br> FROM ';

        if ($this->query->getTable() instanceof Table) {
            $from .= $this->query->getTable()->getName();
        } elseif ($this->query->getTable() instanceof Query) {
            $from .= '(<br><br>' . (string)$this->query->getTable() . '<br<br><br>)';
        }

        if ($this->query->hasTableAlias()) {
            $from .= ' AS ' . $this->query->getTableAlias()->getTo();
        }

        $innerJoin = '';

        if ($this->query->hasInnerJoinedTable()) {
            foreach ($this->query->getInnerJoinedTables() as $innerJoinedTable) {
                $innerJoin .= ' <br>INNER JOIN ';

                if ($innerJoinedTable->getTable() instanceof Table) {
                    $innerJoin .= $innerJoinedTable->getTable()->getName();
                } elseif ($innerJoinedTable->getTable() instanceof Query) {
                    $innerJoin .= '(<br><br>' . (string)$this->query->getTable() . '<br<br><br>)';
                }

                $innerJoin .= $this->printTableAlias($innerJoinedTable);
                $innerJoin .= $this->printOnConditions($innerJoinedTable->getOnConditions());
            }
        }

        $crossJoin = '';

        if ($this->query->hasCrossJoinedTable()) {
            foreach ($this->query->getCrossJoinedTables() as $crossJoinedTable) {
                $crossJoin .= ' <br>CROSS JOIN ';

                if ($crossJoinedTable->getTable() instanceof Table) {
                    $crossJoin .= $crossJoinedTable->getTable()->getName();
                } elseif($crossJoinedTable->getTable() instanceof Query) {
                    $crossJoin .= '(<br><br>' . (string)$this->query->getTable() . '<br<br><br>)';
                }

                $crossJoin .= $this->printTableAlias($crossJoinedTable);
            }
        }

        $leftJoin = '';

        if ($this->query->hasLeftJoinedTable()) {
            foreach ($this->query->getLeftJoinedTables() as $leftJoinedTable) {
                $leftJoin .= ' <br>LEFT JOIN ';

                if ($leftJoinedTable->getTable() instanceof Table) {
                    $leftJoin .= $leftJoinedTable->getTable()->getName();
                } elseif ($leftJoinedTable->getTable() instanceof Query) {
                    $leftJoinedTable .= '(<br><br>' . (string)$this->query->getTable() . '<br<br><br>)';
                }

                $leftJoin .= $this->printTableAlias($leftJoinedTable);
                $leftJoin .= $this->printOnConditions($leftJoinedTable->getOnConditions());
            }
        }

        $rightJoin = '';

        if ($this->query->hasRightJoinedTable()) {
            foreach ($this->query->getRightJoinedTables() as $rightJoinedTable) {
                $rightJoin .= ' <br>RIGHT JOIN ';

                if ($rightJoinedTable->getTable() instanceof Table) {
                    $rightJoin .= $rightJoinedTable->getTable()->getName();
                } elseif ($rightJoinedTable->getTable() instanceof Query) {
                    $rightJoin .= '(<br><br>' . (string)$this->query->getTable() . '<br<br><br>)';
                }

                $rightJoin .= $this->printTableAlias($rightJoinedTable);
                $rightJoin .= $this->printOnConditions($rightJoinedTable->getOnConditions());
            }
        }

        $fullJoin = '';

        if ($this->query->hasFullJoinedTable()) {
            foreach ($this->query->getFullJoinedTables() as $fullJoinedTable) {
                $fullJoin .= ' <br>FULL JOIN ';

                if ($fullJoinedTable->getTable() instanceof Table) {
                    $fullJoin .= $fullJoinedTable->getTable()->getName();
                } elseif ($fullJoinedTable->getTable() instanceof Query) {
                    $fullJoin .= '(<br><br>' . (string)$this->query->getTable() . '<br<br><br>)';
                }

                $fullJoin .= $this->printTableAlias($fullJoinedTable);
                $fullJoin .= $this->printOnConditions($fullJoinedTable->getOnConditions());
            }
        }

        $where = $this->printWhere();

        $orderBy = '';

        if ($this->query->hasOrderBy()) {
            $orderBy = '<br> ORDER BY ';

            foreach ($this->query->getOrderByColumns() as $i => $orderedBy) {
                if ($i !== 0) {
                    $orderBy .= ', ';
                }

                $orderBy .= (string) $orderedBy;
            }
        }

        $groupBy = '';

        if ($this->query->hasGroupBy()) {
            $groupBy = '<br> GROUP BY ';

            foreach ($this->query->getGroupByColumns() as $i => $groupByColumn) {
                if ($i !== 0) {
                    $groupBy .= ', ';
                }

                $groupBy .= (string) $groupByColumn;
            }
        }

        $having = '';

        if ($this->query->hasHavingCondition()) {
            $having = ' <br> HAVING ';

            /**
             * @var Condition $havingCondition
             */
            foreach ($this->query->getHavingConditions() as $havingCondition) {
                $having.= (string) $havingCondition;
            }
        }

        $limit  = $this->printLimit();
        $offset = $this->printOffset();

        $union = '';

        foreach ($this->query->getUnionQueries() as $i => $unionQuery) {
            if ($i === 0) {
                $union .= '<br><br>';
            }

            $union .= ' UNION <br><br>' . (string) $unionQuery;
        }

        $unionAll = '';

        foreach ($this->query->getUnionAllQueries() as $i => $unionAllQuery) {
            if ($i === 0) {
                $unionAll .= '<br><br>';
            }

            $unionAll .= ' UNION ALL <br><br>' . (string) $unionAllQuery;
        }

        $intersect = '';

        foreach ($this->query->getIntersectQueries() as $i => $intersectQuery) {
            if ($i === 0) {
                $intersect .= '<br><br>';
            }

            $intersect .= ' INTERSECT <br><br>' . (string) $intersectQuery;
        }

        $except = '';

        foreach ($this->query->getExceptQueries() as $i => $exceptQuery) {
            if ($i === 0) {
                $except .= '<br><br>';
            }

            $except .= ' EXCEPT <br><br>' . (string) $exceptQuery;
        }

        $selectClause = $select . $functions;
        $joins = $innerJoin . $crossJoin . $leftJoin . $rightJoin . $fullJoin;
        $setOperations = $union . $unionAll . $intersect . $except;
        $limitOperations = $limit . $offset;

        return $selectClause . $from . $joins . $where . $orderBy . $groupBy . $having . $limitOperations . $setOperations;
    }

    /**
     * @return string
     */
    private function insert()
    {
        $columns   = array_keys($this->query->getInsertData());
        $values    = array_values($this->query->getInsertData());
        $tableName = $this->query->getTable()->getName();

        $columns = '(' . implode(', ', $columns). ')';
        $values  = '(' . implode(', ', $values). ')';

        return 'INSERT INTO ' . $tableName . '  ' . $columns . ' VALUES ' . $values . '<br><br>';
    }

    /**
     * @return string
     */
    private function delete()
    {
        $delete = 'DELETE FROM ' . $this->query->getTable()->getName();

        $where  = $this->printWhere();
        $limit  = $this->printLimit();
        $offset = $this->printOffset();

        return $delete . $where . $limit . $offset . '<br><br>';
    }

    /**
     * @return string
     */
    private function update()
    {
        $update = 'UPDATE ' . $this->query->getTable()->getName();

        $set = ' SET ';

        $i = 0;
        $count = count($this->query->getUpdateData());

        foreach ($this->query->getUpdateData() as $column => $value) {
            $i++;

            $set .= $column . ' = ' . $value;

            if ($count !== $i) {
                $set .= ', ';
            }
        }

        $where  = $this->printWhere();
        $limit  = $this->printLimit();
        $offset = $this->printOffset();

        return $update . $set . $where . $limit . $offset . '<br><br>';
    }

    /**
     * @throws Exception
     */
    private function explain()
    {
        return 'EXPLAIN ' . $this->select();
    }

    /**
     * @return string
     */
    private function insertSelect()
    {
        $insert = 'INSERT INTO ' .  $this->query->getTable()->getName();

        $selectQueryPrinter = new QueryPrinter($this->query->getInsertData());
        $select = $selectQueryPrinter->printQuery();

        return $insert . $select;
    }
}
