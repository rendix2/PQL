<?php

/**
 * Class QueryPrinter
 */
class QueryPrinter
{
    /**
     * @var Query
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
     */
    public function printQuery()
    {
        if ($this->query->isSelect()) {
            return $this->select();
        } elseif ($this->query->isInsert()) {
            return $this->insertInto();
        } elseif ($this->query->isDelete()) {
            return $this->delete();
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
     * @param array $conditions
     * @return string
     */
    private function printOnConditions(array $conditions)
    {
        $onCondition = '';

        foreach ($conditions as $i => $condition) {
            if ($i === 0) {
                $onCondition .= ' <br> &nbsp;&nbsp;&nbsp;&nbsp;ON ' . $condition->getColumn() . ' ' . $condition->getOperator() . ' ' . $condition->getValue();
            } else {
                $onCondition .= ' <br> &nbsp;&nbsp;&nbsp;&nbsp;AND ' . $condition->getColumn() . ' ' . $condition->getOperator() . ' ' . $condition->getValue();
            }
        }

        return $onCondition;
    }

    /**
     * @return string
     */
    private function select()
    {
        $select = 'SELECT ' . implode(', ', $this->query->getColumns()) . '<br>';
        $from = ' FROM ' . $this->query->getTable()->getName();

        $innerJoin = '';

        if (count($this->query->getInnerJoin())) {
            foreach ($this->query->getInnerJoin() as $table) {
                $innerJoin .= ' <br>INNER JOIN ' . $table['table']->getName();
                $innerJoin .= $this->printOnConditions($table['onConditions']);
            }
        }

        $crossJoin = '';

        if (count($this->query->getCrossJoin())) {
            foreach ($this->query->getCrossJoin() as $table) {
                $crossJoin .= ' <br>CROSS JOIN ' . $table['table']->getName();
            }
        }

        $leftJoin = '';

        if (count($this->query->getLeftJoin())) {
            foreach ($this->query->getLeftJoin() as $table) {
                $leftJoin .= ' <br>LEFT JOIN ' . $table['table']->getName();
                $leftJoin .= $this->printOnConditions($table['onConditions']);
            }
        }

        $rightJoin = '';

        if (count($this->query->getRightJoin())) {
            foreach ($this->query->getRightJoin() as $table) {
                $rightJoin .= ' <br>RIGHT JOIN ' . $table['table']->getName();
                $rightJoin .= $this->printOnConditions($table['onConditions']);
            }
        }

        $fullJoin = '';

        if (count($this->query->getFullJoin())) {
            foreach ($this->query->getFullJoin() as $table) {
                $fullJoin .= ' <br>FULL JOIN ' . $table['table']->getName();
                $fullJoin .= $this->printOnConditions($table['onConditions']);
            }
        }

        $where = $this->printWhere();

        $orderBy = '';

        if (count($this->query->getOrderBy())) {
            $orderBy = '<br> ORDER BY ';

            foreach ($this->query->getOrderBy() as $orderedBy) {
                $orderBy .= $orderedBy['column'] . ' ' . ($orderedBy['asc'] ? 'ASC' : 'DESC');
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

        if (count($this->query->getHaving())) {
            $having = ' <br> HAVING';

            foreach ($this->query->getHaving() as $havingCondition) {
                $having.= $havingCondition['column'] . ' ' . $havingCondition['operator'] . ' ' . $havingCondition['value'];
            }
        }

        $limit = $this->printLimit();

        return $select . $from . $innerJoin . $crossJoin . $leftJoin . $rightJoin . $fullJoin . $where . $orderBy . $groupBy . $having . $limit . '<br><br>';
    }

    /**
     * @return string
     */
    private function insertInto()
    {
        $columns = array_keys($this->query->getInsertData());
        $values  = array_values($this->query->getInsertData());

        $columns = '(' . implode(', ', $columns). ')';
        $values = '(' . implode(', ', $values). ')';

        return 'INSERT INTO ' . $columns . ' VALUES ' . $values;
    }

    /**
     * @return string
     */
    private function delete()
    {
        $delete = 'DELETE FROM ' . $this->query->getTable()->getName();

        $where = $this->printWhere();
        $limit = $this->printLimit();

        return $delete . $where . $limit;
    }
}