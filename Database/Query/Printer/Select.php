<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Select.php
 * User: Tomáš Babický
 * Date: 28.08.2021
 * Time: 2:27
 */

namespace PQL\Query\Printer;


use Exception;
use Netpromotion\Profiler\Profiler;
use PQL\Query\Builder\Expressions\AbstractExpression;
use PQL\Query\Builder\Expressions\QueryExpression;
use PQL\Query\Builder\Expressions\TableExpression;
use PQL\Query\Builder\Expressions\WhereCondition;
use PQL\Query\Builder\Select as SelectBuilder;

class Select
{

    private string $clauseSpan;
    private string $closeSpan;
    private string $columnSpan;
    private string $operatorSpan;
    private string $tableSpan;

    private SelectBuilder $query;

    private int $indent;

    public function __construct(SelectBuilder $select, int $indent)
    {
        $this->query = $select;
        $this->indent = $indent;

        $this->tableSpan = '<span style="color: orangered">';
        $this->columnSpan = '<span style="color: darkmagenta">';
        $this->clauseSpan = '<span style="color: blue">';
        $this->operatorSpan = '<span style="color:darkgray">';
        $this->closeSpan = '</span>';
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function indent(int $indent = null): string
    {
        if ($indent) {
            return str_repeat("&nbsp;", 3 * $indent);
        } else {
            return str_repeat("&nbsp;", 3 * $this->indent);
        }
    }

    private function alias(AbstractExpression $expression) : string
    {
        return $expression->getAlias() ? $this->clauseSpan . ' AS ' . $this->closeSpan . $expression->getAlias() : '';
    }

    private function select() : string
    {
        $select = $this->clauseSpan . 'SELECT' . $this->closeSpan . ' ';

        if ($this->query->getDistinct()) {
            $select .= $this->clauseSpan . 'DISTINCT' . $this->closeSpan . $this->columnSpan . ' ' . $this->query->getDistinct()->evaluate() . $this->closeSpan;
        } else {
            $count = count($this->query->getColumns());

            foreach ($this->query->getColumns() as $i => $column) {
                $select .= '<br>' . $this->indent() . $this->indent();
                $select .= $this->columnSpan . $column->print() . $this->closeSpan;
                $select .= $this->alias($column);

                if ($i !== $count - 1) {
                    $select .= ', ';
                }
            }
        }

        return $this->indent() . $select;
    }

    private function from() : string
    {
        $fromString = '<br>' . $this->indent() . $this->clauseSpan . 'FROM' . $this->closeSpan . ' ';

        $fromClause = $this->query->getFromClause();

         if ($fromClause instanceof TableExpression) {
             $fromString .= $fromClause->print();
         } elseif ($fromClause instanceof QueryExpression) {
             $fromString .= '(<br>' . $fromClause->print($this->indent + 1) . $this->indent() . ')';
         } else {
             throw new Exception('Unknown FROM Clause');
         }

         $fromString .= $this->alias($fromClause);

         return $fromString;
    }

    private function leftJoins() : string
    {
        $leftJoined = '';

        foreach ($this->query->getLeftJoinedTables() as $leftJoinedTable) {
            if ($leftJoinedTable->getJoinExpression() instanceof TableExpression) {
                $leftJoined .= '<br>' . $this->indent() . $this->clauseSpan . 'LEFT JOIN' . $this->closeSpan . ' ' . $this->tableSpan . $leftJoinedTable->getJoinExpression()->getTable()->getName() . $this->closeSpan;
                $leftJoined .= $this->alias($leftJoinedTable->getJoinExpression());
                $leftJoined .= $this->onConditions($leftJoinedTable->getJoinConditions());
            } elseif ($leftJoinedTable->getJoinExpression() instanceof QueryExpression) {
                $printer = new Select($leftJoinedTable->getJoinExpression()->getQuery(), $this->indent + 1);

                $leftJoined .= '<br>' . $this->indent() . $this->clauseSpan . 'LEFT JOIN' . $this->closeSpan . ' (<br>' . $printer->print() . '<br>' . $this->indent() . ')';
                $leftJoined .= $this->alias($leftJoinedTable->getJoinExpression());
                $leftJoined .= $this->onConditions($leftJoinedTable->getJoinConditions());
            }
        }

        return $leftJoined;
    }

    private function innerJoins() : string
    {
        $innerJoined = '';

        foreach ($this->query->getInnerJoinedTables() as $innerJoinedTable) {
            if ($innerJoinedTable->getJoinExpression() instanceof TableExpression) {
                $innerJoined .= '<br>' . $this->indent() . $this->clauseSpan . 'INNER JOIN'. $this->closeSpan . ' ' . $this->tableSpan . $innerJoinedTable->getJoinExpression()->getTable()->getName() . $this->closeSpan;
                $innerJoined .= $this->onConditions($innerJoinedTable->getJoinConditions());
            } elseif ($innerJoinedTable->getJoinExpression() instanceof QueryExpression) {
                $printer = new Select($innerJoinedTable->getJoinExpression()->getQuery(), $this->indent + 1);

                $innerJoined .= '<br>' . $this->indent() . $this->clauseSpan . 'INNER JOIN'. $this->closeSpan . ' (<br>' . $printer->print() . '<br>' . $this->indent() . ')';
                $innerJoined .= $this->onConditions($innerJoinedTable->getJoinConditions());
            }
        }

        return $innerJoined;
    }

    private function crossJoins() : string
    {
        $crossJoined = '';

        foreach ($this->query->getCrossJoinedTables() as $crossJoinedTable) {
            if ($crossJoinedTable->getJoinExpression() instanceof TableExpression) {
                $crossJoined .= '<br>' . $this->indent() . $this->clauseSpan . 'CROSS JOIN'. $this->closeSpan . ' ' . $this->tableSpan . $crossJoinedTable->getJoinExpression()->getTable()->getName() . $this->closeSpan;
            } elseif ($crossJoinedTable->getJoinExpression() instanceof QueryExpression) {
                $printer = new Select($crossJoinedTable->getJoinExpression()->getQuery(), $this->indent + 1);

                $crossJoined .= '<br>' . $this->indent() . $this->clauseSpan . 'CROSS JOIN'. $this->closeSpan . ' (<br>' . $printer->print() . '<br>' . $this->indent() . ')';
            }
        }

        return $crossJoined;
    }

    /**
     * @param WhereCondition[] $joinConditions
     *
     * @return string
     */
    private function onConditions(array $joinConditions) : string
    {
        $onConditions = '';

        foreach ($joinConditions as $i => $condition) {
            $leftPart = $condition->getLeft()->evaluate();
            $rightPart = $condition->getRight()->evaluate();
            $operator = $condition->getOperator()->evaluate();

            if ($i === 0) {
                $onConditions .= '<br>' . $this->indent() . $this->indent() . $this->clauseSpan . 'ON' . $this->closeSpan . ' ';
            } else {
                $onConditions .= '<br>' . $this->indent() . $this->indent() . $this->clauseSpan . 'AND' . $this->closeSpan . ' ';
            }

            $onConditions .= $this->columnSpan . $leftPart . $this->closeSpan . ' ' . $this->operatorSpan . $operator .  $this->closeSpan .' ' . $this->columnSpan . $rightPart . $this->closeSpan;
        }

        return $onConditions;
    }

    private function where() : string
    {
        $where = '<br>' . $this->indent() . $this->clauseSpan . 'WHERE' . $this->closeSpan . ' ';

        foreach ($this->query->getWhereConditions() as $i => $whereCondition) {
            $leftPart = $whereCondition->getLeft()->evaluate();
            $operator = $whereCondition->getOperator()->getOperator();

            if ($whereCondition->getRight()) {
                $rightPart = $whereCondition->getRight()->print();
            } else {
                $rightPart = '';
            }

            if ($i !== 0) {
                $where .= '<br>' . $this->indent() . $this->indent()  . $this->clauseSpan . 'AND ' . $this->closeSpan;
            }

            $where .= $this->columnSpan . $leftPart . $this->closeSpan . ' ' . $this->operatorSpan . $operator .  $this->closeSpan .' ' . $this->columnSpan . $rightPart . $this->closeSpan;
        }

        return count($this->query->getWhereConditions()) ? $where : '';
    }

    private function orderBy() : string
    {
        $orderBy = '<br>' . $this->indent() . $this->clauseSpan . 'ORDER BY' . $this->closeSpan;

        foreach ($this->query->getOrderByColumns() as $orderByColumn) {
            $asc = $orderByColumn->isAsc() ? 'ASC' : 'DESC';
            $orderBy .= ' ' . $this->columnSpan . $orderByColumn->getExpression()->evaluate() . $this->closeSpan . ' ' . $asc;
        }

        return count($this->query->getOrderByColumns()) ? $orderBy : '';
    }

    private function groupBy() : string
    {
        $groupBy = '<br>' . $this->indent() . $this->clauseSpan . 'GROUP BY' . $this->closeSpan;

        $count = count($this->query->getGroupByColumns());

        foreach ($this->query->getGroupByColumns() as $i => $column) {
            $groupBy .= ' ' . $column->evaluate();

            if ($count !== $i && $i !== 0) {
                $groupBy .= ',';
            }
        }
        return $count ? $groupBy : '';
    }

    private function having() : string
    {
        $having = '<br>' . $this->indent() . $this->clauseSpan . 'HAVING' . $this->closeSpan . ' ';

        foreach ($this->query->getHavingConditions() as $i => $whereCondition) {
            $leftPart = $whereCondition->getLeft()->evaluate();
            $operator = $whereCondition->getOperator()->getOperator();

            if ($whereCondition->getRight()) {
                $rightPart = $whereCondition->getRight()->print();
            } else {
                $rightPart = '';
            }

            if ($i !== 0) {
                $having .= '<br>' . $this->indent() . $this->indent()  . $this->clauseSpan . 'AND ' . $this->closeSpan;
            }

            $having .= $this->columnSpan . $leftPart . $this->closeSpan . ' ' . $this->operatorSpan . $operator .  $this->closeSpan .' ' . $this->columnSpan . $rightPart . $this->closeSpan;
        }

        return count($this->query->getHavingConditions()) ? $having : '';
    }

    private function limit() : string
    {
        if ($this->query->getLimit()) {
            return '<br>' . $this->indent() . $this->clauseSpan . 'LIMIT ' .$this->closeSpan . $this->query->getLimit();
        }

        return '';
    }

    private function offset() : string
    {
        if ($this->query->getOffset()) {
            return '<br>' . $this->indent() . $this->clauseSpan . 'OFFSET ' . $this->closeSpan . $this->query->getOffset();
        }

        return '';
    }

    private function intersect() : string
    {
        $intersect = '';

        if (count($this->query->getIntersected())) {
            $intersect = '<br><br> '.$this->clauseSpan . 'INTERSECT '. $this->closeSpan . '<br><br>';

            foreach ($this->query->getIntersected() as $intersectQuery) {
                $printer = new Select($intersectQuery, $this->indent);

                $intersect .= $printer->print();
            }
        }

        return $intersect;
    }

    private function except() : string
    {
        $excepted = '';

        if (count($this->query->getExceptedQueries())) {
            $excepted = '<br><br> ' . $this->clauseSpan . 'EXCEPT ' . $this->closeSpan . '<br><br>';

            foreach ($this->query->getExceptedQueries() as $exceptedQuery) {
                $printer = new Select($exceptedQuery, $this->indent);

                $excepted .= $printer->print();
            }
        }

        return $excepted;
    }

    private function union() : string
    {
        $unioned = '';

        if (count($this->query->getUnionedQueries())) {
            $unioned = '<br><br> ' . $this->clauseSpan . 'UNION ' . $this->closeSpan . '<br><br>';

            foreach ($this->query->getUnionedQueries() as $exceptedQuery) {
                $printer = new Select($exceptedQuery, $this->indent);

                $unioned .= $printer->print();
            }
        }

        return $unioned;
    }

    private function unionAll() : string
    {
        $unioned = '';

        if (count($this->query->getUnionedAllQueries())) {
            $unioned = '<br><br> '.$this->clauseSpan . 'UNION ALL '. $this->closeSpan . '<br><br>';

            foreach ($this->query->getUnionedAllQueries() as $exceptedQuery) {
                $printer = new Select($exceptedQuery, $this->indent);

                $unioned .= $printer->print();
            }
        }

        return $unioned;
    }


    public function print() : string
    {
        Profiler::start('Print query');

        $query = '<div style="background-color:#fdf9e2">';

        $query .= $this->select();
        $query .= $this->from();
        $query .= $this->innerJoins();
        $query .= $this->crossJoins();
        $query .= $this->leftJoins();
        $query .= $this->where();

        $query .= $this->orderBy();
        $query .= $this->groupBy();

        $query .= $this->having();

        $query .= $this->limit();
        $query .= $this->offset();

        $query .= $this->intersect();
        $query .= $this->except();
        $query .= $this->union();
        $query .= $this->unionAll();

        $query .= '</div>';
        Profiler::finish('Print query');

        return $query;
    }
}