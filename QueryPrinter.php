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
                return $this->insertInto();
            case Query::DELETE:
                return $this->delete();
            case Query::UPDATE:
                return $this->update();
            case Query::EXPLAIN:
                return $this->explain();
            default:
                return 'Unknown query type.';
        }
    }

    /**
     * @return string
     */
    private function printWhere()
    {
        $whereCount = count($this->query->getWhereCondition());
        $where = '';

        if ($whereCount) {
            $where = ' <br>WHERE ';

            --$whereCount;

            foreach ($this->query->getWhereCondition() as $i => $whereCondition) {
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
     * @param Table $table
     *
     * @return string
     */
    private function printTableAlias(Table $table)
    {
        $tableAlias = Alias::findAliasForTable($table, $this->query->getAliases());

        if ($tableAlias) {
            $tableAlias = ' AS ' . $tableAlias->getTo();
        }

        return $tableAlias;
    }

    /**
     * @return string
     */
    private function select()
    {
        $select = 'SELECT ' . implode(', ', $this->query->getColumns());
        $functions = '';

        $columnsCount = count($this->query->getColumns());

        /**
         * @var FunctionPql $function
         */
        foreach ($this->query->getFunctions() as $i => $function) {
            if (($i === 0 && $columnsCount) || $i > 0) {
                $functions .= ', ' . mb_strtoupper($function->getName()) . '(' . implode(', ', $function->getParams()) . ')';
            } elseif ($i === 0 && !$columnsCount) {
                $functions .= mb_strtoupper($function->getName()) . '(' . implode(', ', $function->getParams()) . ')';
            }
        }

        $from = '<br> FROM ' . $this->query->getTable()->getName() . $this->printTableAlias($this->query->getTable());

        $innerJoin = '';

        if (count($this->query->getInnerJoin())) {
            foreach ($this->query->getInnerJoin() as $table) {
                $innerJoin .= ' <br>INNER JOIN ' . $table['table']->getName() . $this->printTableAlias($table['table']);
                $innerJoin .= $this->printOnConditions($table['onConditions']);
            }
        }

        $crossJoin = '';

        if (count($this->query->getCrossJoin())) {
            foreach ($this->query->getCrossJoin() as $table) {
                $crossJoin .= ' <br>CROSS JOIN ' . $table['table']->getName() . $this->printTableAlias($table['table']);
            }
        }

        $leftJoin = '';

        if (count($this->query->getLeftJoin())) {
            foreach ($this->query->getLeftJoin() as $table) {
                $leftJoin .= ' <br>LEFT JOIN ' . $table['table']->getName() . $this->printTableAlias($table['table']);
                $leftJoin .= $this->printOnConditions($table['onConditions']);
            }
        }

        $rightJoin = '';

        if (count($this->query->getRightJoin())) {
            foreach ($this->query->getRightJoin() as $table) {
                $rightJoin .= ' <br>RIGHT JOIN ' . $table['table']->getName() . $this->printTableAlias($table['table']);
                $rightJoin .= $this->printOnConditions($table['onConditions']);
            }
        }

        $fullJoin = '';

        if (count($this->query->getFullJoin())) {
            foreach ($this->query->getFullJoin() as $table) {
                $fullJoin .= ' <br>FULL JOIN ' . $table['table']->getName() . $this->printTableAlias($table['table']);
                $fullJoin .= $this->printOnConditions($table['onConditions']);
            }
        }

        $where = $this->printWhere();

        $orderBy = '';

        if (count($this->query->getOrderBy())) {
            $orderBy = '<br> ORDER BY ';

            foreach ($this->query->getOrderBy() as $orderedBy) {
                $orderBy .= (string) $orderedBy;
            }
        }

        $groupBy = '';

        if (count($this->query->getGroupBy())) {
            $groupBy = '<br> GROUP BY ';

            foreach ($this->query->getGroupBy() as $groupedBy) {
                $groupBy .= $groupedBy . ' ';
            }
        }

        $having = '';

        if (count($this->query->getHavingConditions())) {
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

        return $select . $functions . $from . $innerJoin . $crossJoin . $leftJoin . $rightJoin . $fullJoin . $where . $orderBy . $groupBy . $having . $limit . $offset . '<br><br>';
    }

    /**
     * @return string
     */
    private function insertInto()
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
    public function update()
    {
        $update = 'UPDATE ' . $this->query->getTable();

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
    public function explain()
    {
        return 'EXPLAIN ' . $this->select();
    }
}