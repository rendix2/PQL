<?php
/**
 *
 * Created by PhpStorm.
 * Filename: SelectPrinter.php
 * User: Tomáš Babický
 * Date: 28.08.2021
 * Time: 2:27
 */

namespace PQL\Database\Query\Printer;

use Exception;
use Netpromotion\Profiler\Profiler;
use PQL\Database\Query\Builder\Expressions\AbstractExpression;
use PQL\Database\Query\Builder\Expressions\Column;
use PQL\Database\Query\Builder\Expressions\IExpression;
use PQL\Database\Query\Builder\Expressions\QueryExpression;
use PQL\Database\Query\Builder\Expressions\Set;
use PQL\Database\Query\Builder\Expressions\TableExpression;
use PQL\Database\Query\Builder\Expressions\WhereCondition;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Printer\Colorizer\IColorizer;
use PQL\Database\Query\Printer\Colorizer\StandardColorizer;
use PQL\Database\Query\Scheduler\Scheduler;

/**
 * Class SelectPrinter
 *
 * @package PQL\Database\Query\Printer
 */
class SelectPrinter
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @var int $indent
     */
    private int $indent;

    /**
     * @var IColorizer $colorizer
     */
    private IColorizer $colorizer;

    /**
     * @param SelectBuilder $select
     * @param int           $indent
     */
    public function __construct(
        SelectBuilder $select,
        int $indent,
    ) {
        $this->query = $select;
        $this->indent = $indent;

        $this->colorizer = new StandardColorizer();
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
        return $expression->getAlias() ? $this->colorizer->getClauseSpan() . ' AS ' . $this->colorizer->getCloseSpan() . $expression->getAlias() : '';
    }

    private function select() : string
    {
        $selectString = $this->colorizer->getClauseSpan() . 'SELECT' . $this->colorizer->getCloseSpan() . ' ';

        if ($this->query->getDistinct()) {
            $selectString .= $this->colorizer->getClauseSpan() . 'DISTINCT' . $this->colorizer->getCloseSpan() . $this->colorizer->getColumnSpan() . ' ' . $this->query->getDistinct()->evaluate() . $this->colorizer->getCloseSpan();
        } else {
            $that = $this;

            $selectArray = array_map(
                static function (IExpression $expression) use ($that) {
                    $select = '<br>' . $that->indent() . $that->indent();
                    $select .= $that->colorizer->getColumnSpan() . $expression->print() . $that->colorizer->getCloseSpan();
                    $select .= $that->alias($expression);

                    return $select;
                }, $this->query->getColumns()
            );

            $selectString .= implode(',', $selectArray);
        }

        return $this->indent() . $selectString;
    }

    /**
     * @return string
     * @throws Exception
     */
    private function from() : string
    {
        $fromString = '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'FROM' . $this->colorizer->getCloseSpan() . ' ';

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
                $leftJoined .= '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'LEFT JOIN' . $this->colorizer->getCloseSpan();
                $leftJoined .= ' ' . $this->colorizer->getTableSpan() . $leftJoinedTable->getJoinExpression()->getTable()->getName() . $this->colorizer->getCloseSpan();
                $leftJoined .= $this->alias($leftJoinedTable->getJoinExpression());
                $leftJoined .= $this->onConditions($leftJoinedTable->getJoinConditions());
            } elseif ($leftJoinedTable->getJoinExpression() instanceof QueryExpression) {
                $printer = new SelectPrinter($leftJoinedTable->getJoinExpression()->getQuery(), $this->indent + 1);

                $leftJoined .= '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'LEFT JOIN' . $this->colorizer->getCloseSpan() . ' (<br>' . $printer->print() . '<br>' . $this->indent() . ')';
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
                $innerJoined .= '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'INNER JOIN' . $this->colorizer->getCloseSpan();
                $innerJoined .= ' ' . $this->colorizer->getTableSpan() . $innerJoinedTable->getJoinExpression()->getTable()->getName() . $this->colorizer->getCloseSpan();
                $innerJoined .= $this->onConditions($innerJoinedTable->getJoinConditions());
            } elseif ($innerJoinedTable->getJoinExpression() instanceof QueryExpression) {
                $printer = new SelectPrinter($innerJoinedTable->getJoinExpression()->getQuery(), $this->indent + 1);

                $innerJoined .= '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'INNER JOIN' . $this->colorizer->getCloseSpan() . ' (<br>' . $printer->print() . '<br>' . $this->indent() . ')';
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
                $crossJoined .= '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'CROSS JOIN' . $this->colorizer->getCloseSpan();
                $crossJoined .= ' ' . $this->colorizer->getTableSpan() . $crossJoinedTable->getJoinExpression()->getTable()->getName() . $this->colorizer->getCloseSpan();
            } elseif ($crossJoinedTable->getJoinExpression() instanceof QueryExpression) {
                $printer = new SelectPrinter($crossJoinedTable->getJoinExpression()->getQuery(), $this->indent + 1);

                $crossJoined .= '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'CROSS JOIN' . $this->colorizer->getCloseSpan() . ' (<br>' . $printer->print() . '<br>' . $this->indent() . ')';
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
                $onConditions .= '<br>' . $this->indent() . $this->indent() . $this->colorizer->getClauseSpan() . 'ON' . $this->colorizer->getCloseSpan() . ' ';
            } else {
                $onConditions .= '<br>' . $this->indent() . $this->indent() . $this->colorizer->getClauseSpan() . 'AND' . $this->colorizer->getCloseSpan() . ' ';
            }

            if (is_array($leftPart)) {
                $leftOperand = '(' . implode(', ', $leftPart) . ')';
            } else {
                $leftOperand = $leftPart;
            }

            if (is_array($rightPart)) {
                $rightOperand = '(' . implode(', ', $rightPart) . ')';
            } else {
                $rightOperand = $rightPart;
            }

            $onConditions .= $this->colorizer->getColumnSpan() . $leftOperand . $this->colorizer->getCloseSpan() . ' ' . $this->colorizer->getOperatorSpan() . $operator . $this->colorizer->getCloseSpan() . ' ' . $this->colorizer->getColumnSpan() . $rightOperand . $this->colorizer->getCloseSpan();
        }

        return $onConditions;
    }

    private function where() : string
    {
        $where = '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'WHERE' . $this->colorizer->getCloseSpan() . ' ';

        foreach ($this->query->getWhereConditions() as $i => $whereCondition) {
            $leftPart = $whereCondition->getLeft()->evaluate();
            $operator = $whereCondition->getOperator();
            $operatorString = $operator->getOperator();

            if ($whereCondition->getRight()) {
                $rightPart = $whereCondition->getRight()->print();
            } else {
                $rightPart = '';
            }

            if ($i !== 0) {
                $where .= '<br>' . $this->indent() . $this->indent() . $this->colorizer->getClauseSpan() . 'AND ' . $this->colorizer->getCloseSpan();
            }

            if ($operator->isUnary()) {
                $where .= $this->colorizer->getColumnSpan() . $leftPart . $this->colorizer->getClauseSpan() . ' ' . $this->colorizer->getOperatorSpan() . $operatorString . $this->colorizer->getCloseSpan() . ' ' . $this->colorizer->getColumnSpan() . $this->colorizer->getCloseSpan();
            } elseif ($operator->isBinary()) {
                $where .= $this->colorizer->getColumnSpan() . $leftPart . $this->colorizer->getClauseSpan() . ' ' . $this->colorizer->getOperatorSpan() . $operatorString . $this->colorizer->getCloseSpan() . ' ' . $this->colorizer->getColumnSpan() . $rightPart . $this->colorizer->getCloseSpan();
            }
        }

        return count($this->query->getWhereConditions()) ? $where : '';
    }

    private function orderBy() : string
    {
        $orderBy = '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'ORDER BY' . $this->colorizer->getCloseSpan();

        foreach ($this->query->getOrderByColumns() as $orderByColumn) {
            $asc = $orderByColumn->isAsc() ? 'ASC' : 'DESC';
            $orderBy .= ' ' . $this->colorizer->getColumnSpan() . $orderByColumn->getExpression()->evaluate() . $this->colorizer->getCloseSpan() . ' ' . $asc;
        }

        return count($this->query->getOrderByColumns()) ? $orderBy : '';
    }

    private function groupBy() : string
    {
        $groupBy = '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'GROUP BY' . $this->colorizer->getCloseSpan();

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
        $having = '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'HAVING' . $this->colorizer->getCloseSpan() . ' ';

        foreach ($this->query->getHavingConditions() as $i => $whereCondition) {
            $leftPart = $whereCondition->getLeft()->print();
            $operator = $whereCondition->getOperator()->getOperator();

            if ($whereCondition->getRight()) {
                $rightPart = $whereCondition->getRight()->print();
            } else {
                $rightPart = '';
            }

            if ($i !== 0) {
                $having .= '<br>' . $this->indent() . $this->indent() . $this->colorizer->getClauseSpan() . 'AND ' . $this->colorizer->getCloseSpan();
            }

            $having .= $this->colorizer->getColumnSpan() . $leftPart . $this->colorizer->getCloseSpan() . ' ' . $this->colorizer->getOperatorSpan() . $operator . $this->colorizer->getCloseSpan() . ' ' . $this->colorizer->getColumnSpan() . $rightPart . $this->colorizer->getCloseSpan();
        }

        return count($this->query->getHavingConditions()) ? $having : '';
    }

    private function limit() : string
    {
        if ($this->query->getLimit()) {
            return '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'LIMIT ' . $this->colorizer->getCloseSpan() . $this->query->getLimit();
        }

        return '';
    }

    private function offset() : string
    {
        if ($this->query->getOffset()) {
            return '<br>' . $this->indent() . $this->colorizer->getClauseSpan() . 'OFFSET ' . $this->colorizer->getCloseSpan() . $this->query->getOffset();
        }

        return '';
    }

    private function intersect() : string
    {
        $intersect = '';

        if (count($this->query->getIntersected())) {
            $intersect = '<br><br> ' . $this->colorizer->getClauseSpan() . 'INTERSECT ' . $this->colorizer->getCloseSpan() . '<br><br>';

            foreach ($this->query->getIntersected() as $intersectQuery) {
                $printer = new SelectPrinter($intersectQuery, $this->indent);

                $intersect .= $printer->print();
            }
        }

        return $intersect;
    }

    private function except() : string
    {
        $excepted = '';

        if (count($this->query->getExceptedQueries())) {
            $excepted = '<br><br> ' . $this->colorizer->getClauseSpan() . 'EXCEPT ' . $this->colorizer->getCloseSpan() . '<br><br>';

            foreach ($this->query->getExceptedQueries() as $exceptedQuery) {
                $printer = new SelectPrinter($exceptedQuery, $this->indent);

                $excepted .= $printer->print();
            }
        }

        return $excepted;
    }

    private function union() : string
    {
        $unioned = '';

        if (count($this->query->getUnionedQueries())) {
            $unioned = '<br><br> ' . $this->colorizer->getClauseSpan() . 'UNION ' . $this->colorizer->getCloseSpan() . '<br><br>';

            foreach ($this->query->getUnionedQueries() as $exceptedQuery) {
                $printer = new SelectPrinter($exceptedQuery, $this->indent);

                $unioned .= $printer->print();
            }
        }

        return $unioned;
    }

    private function unionAll() : string
    {
        $unioned = '';

        if (count($this->query->getUnionedAllQueries())) {
            $unioned = '<br><br> ' . $this->colorizer->getClauseSpan() . 'UNION ALL ' . $this->colorizer->getCloseSpan() . '<br><br>';

            foreach ($this->query->getUnionedAllQueries() as $exceptedQuery) {
                $printer = new SelectPrinter($exceptedQuery, $this->indent);

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