<?php
/**
 *
 * Created by PhpStorm.
 * Filename: SelectPrinter.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 10:41
 */

namespace PQL\Database\Query\Builder;

use Exception;
use PQL\Database\Database;
use PQL\Database\Query\Builder\Expressions\Column;
use PQL\Database\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Database\Query\Builder\Expressions\FunctionExpression;
use PQL\Database\Query\Builder\Expressions\HavingCondition;
use PQL\Database\Query\Builder\Expressions\IExpression;
use PQL\Database\Query\Builder\Expressions\IFromExpression;
use PQL\Database\Query\Builder\Expressions\IMathBinaryOperator;
use PQL\Database\Query\Builder\Expressions\IMathExpression;
use PQL\Database\Query\Builder\Expressions\ISelect;
use PQL\Database\Query\Builder\Expressions\IValue;
use PQL\Database\Query\Builder\Expressions\WhereCondition;
use PQL\Database\Query\Printer\SelectPrinter;
use PQL\Database\Query\Result\TableResult;
use PQL\Database\Query\Executor\SelectExecutor;
use PQL\Query\ExplainExecutor;
use SplStack;
use stdClass;

/**
 * Class SelectBuilder
 *
 * @package PQL\Database\Query\Builder
 */
class SelectBuilder
{
    /**
     * @var Database $database
     */
    private Database $database;

    /**
     * @var ISelect[] $columns
     */
    private array $columns;

    /**
     * @var ?IExpression $distinct
     */
    private ?IExpression $distinct;

    /**
     * @var AggregateFunctionExpression[] $aggregateFunctions
     */
    private array $aggregateFunctions;

    /**
     * @var SelectBuilder[] $exceptedQueries
     */
    private array $exceptedQueries;

    /**
     * @var bool $executed
     */
    private bool $executed;

    /**
     * @var FunctionExpression[] $functions
     */
    private array $functions;

    /**
     * @var Column[] $groupByColumns
     */
    private array $groupByColumns;

    /**
     * @var HavingCondition[] $havingConditions
     */
    private array $havingConditions;

    /**
     * @var SelectBuilder[] $intersected
     */
    private array $intersected;

    /**
     * @var JoinExpression[] $leftJoinedTables
     */
    private array $leftJoinedTables;

    /**
     * @var OrderByExpression[] $orderByColumns
     */
    private array $orderByColumns;

    /**
     * @var JoinExpression[] $rightJoinedTables
     */
    private array $rightJoinedTables;

    /**
     * @var JoinExpression[] $innerJoinedTables
     */
    private array $innerJoinedTables;

    /**
     * @var JoinExpression[] $crossJoinedTables
     */
    private array $crossJoinedTables;

    /**
     * @var ?IFromExpression $fromClause
     */
    private ?IFromExpression $fromClause;

    /**
     * @var SelectBuilder[] $unionedAllQueries
     */
    private array $unionedAllQueries;

    /**
     * @var SelectBuilder[] $unionedQueries
     */
    private array $unionedQueries;

    /**
     * @var IValue[] $values
     */
    private array $values;

    /**
     * @var IMathBinaryOperator[] $mathBinaryOperators
     */
    private array $mathBinaryOperators;

    /**
     * @var WhereCondition[] $whereConditions
     */
    private array $whereConditions;

    /**
     * @var ?int limit
     */
    private ?int $limit;

    /**
     * @var ?int offset
     */
    private ?int $offset;

    /**
     * @var stdClass[] $result
     */
    private array $result;

    /**
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;

        $this->fromClause = null;

        $this->whereConditions = [];
        $this->havingConditions = [];

        $this->leftJoinedTables = [];
        $this->rightJoinedTables = [];
        $this->innerJoinedTables = [];
        $this->crossJoinedTables = [];

        $this->orderByColumns = [];
        $this->groupByColumns = [];

        $this->aggregateFunctions = [];
        $this->functions = [];
        $this->values = [];
        $this->mathBinaryOperators = [];

        $this->limit = null;
        $this->offset = null;

        $this->columns = [];
        $this->distinct = null;

        $this->intersected = [];
        $this->exceptedQueries = [];
        $this->unionedAllQueries = [];
        $this->unionedQueries = [];

        $this->result = [];
        $this->executed = false;
    }

    /**
     * SelectBuilder destructor.
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->printQuery();
    }

    /**
     * @param IExpression $expression
     *
     * @return $this
     */
    public function select(IExpression $expression) : static
    {
        $this->columns[] = $expression;

        if ($expression instanceof AggregateFunctionExpression) {
            $this->aggregateFunctions[$expression->print()] = $expression;
        }

        if ($expression instanceof FunctionExpression) {
            $this->functions[] = $expression;
        }

        if ($expression instanceof IValue) {
            if ($this->isMathExpressionOnly($expression)) {
                $this->values[] = $expression;
            }
        }

        if ($expression instanceof IMathBinaryOperator) {
            $this->mathBinaryOperators[] = $expression;

            $aggregateFunctions = $this->findAggregateFunctionsInMathExpressions($expression);

            foreach ($aggregateFunctions as $aggregateFunction) {
                $this->aggregateFunctions[$aggregateFunction->print()] = $aggregateFunction;
            }
        }

        return $this;
    }

    private function isMathExpressionOnly(?IExpression $expression = null) : bool
    {
        $res = true;

        if ($expression === null) {
            return $res;
        }

        if (!($expression instanceof IMathExpression) && !($expression instanceof IMathBinaryOperator) && !($expression instanceof IValue) ) {
            return false;
        }

        if ($expression instanceof IMathExpression) {
            if (method_exists($expression, 'getLeft')) {
                return $this->isMathExpressionOnly($expression->getLeft());
            }

            if (method_exists($expression, 'getRight')) {
                return $this->isMathExpressionOnly($expression->getRight());
            }
        }

        return $res;
    }

    /**
     * @param IExpression|null $expression
     *
     * @return array
     */
    private function findAggregateFunctionsInMathExpressions(?IExpression $expression = null) : array
    {
        $expressions = [];

        if ($expression === null) {
            return $expressions;
        }

        if ($expression instanceof IMathExpression) {
            if (method_exists($expression, 'getLeft')) {
                return $this->findAggregateFunctionsInMathExpressions($expression->getLeft());
            }

            if (method_exists($expression, 'getRight')) {
                return $this->findAggregateFunctionsInMathExpressions($expression->getRight());
            }
        }

        if ($expression instanceof AggregateFunctionExpression) {
            $expressions[] = $expression;
        }

        return $expressions;
    }

    /**
     * @param IExpression $expression
     *
     * @return $this
     */
    public function distinct(IExpression $expression) : static
    {
        $this->distinct = $expression;
        $this->columns = [$expression];

        if ($expression instanceof AggregateFunctionExpression) {
            $this->aggregateFunctions[$expression->print()] = $expression;
        }

        if ($expression instanceof FunctionExpression) {
            $this->functions[] = $expression;
        }

        if ($expression instanceof IValue) {
            if ($this->isMathExpressionOnly($expression)) {
                $this->values[] = $expression;
            }
        }

        if ($expression instanceof IMathExpression) {
            $aggregateFunctions = $this->findAggregateFunctionsInMathExpressions($expression);

            foreach ($aggregateFunctions as $aggregateFunction) {
                $this->aggregateFunctions[$aggregateFunction->print()] = $aggregateFunction;
            }
        }

        return $this;
    }

    /**
     * @param IFromExpression $fromClause
     *
     * @return $this
     */
    public function from(IFromExpression $fromClause) : static
    {
        $this->fromClause = $fromClause;

        return $this;
    }

    public function crossJoin(IFromExpression $table) : static
    {
        $this->crossJoinedTables[] = new JoinExpression($table, []);

        return $this;
    }

    public function leftJoin(IFromExpression $table, array $joinConditions) : static
    {
        $this->leftJoinedTables[] = new JoinExpression($table, $joinConditions);

        return $this;
    }

    public function rightJoin(IFromExpression $table, array $joinConditions) : static
    {
        $this->rightJoinedTables[] = new JoinExpression($table, $joinConditions);

        return $this;
    }

    public function innerJoin(IFromExpression $table, array $joinConditions) : static
    {
        $this->innerJoinedTables[] = new JoinExpression($table, $joinConditions);

        return $this;
    }

    public function where(WhereCondition $whereCondition) : static
    {
        $this->whereConditions[] = $whereCondition;

        return $this;
    }

    public function groupBy(Column $column) : static
    {
        $this->groupByColumns[] = $column;

        return $this;
    }

    public function having(HavingCondition $havingCondition) : static
    {
        $this->havingConditions[] = $havingCondition;

        if ($havingCondition->getLeft() instanceof IMathBinaryOperator) {
            $aggregateFunctions = $this->findAggregateFunctionsInMathExpressions($havingCondition->getLeft());

            foreach ($aggregateFunctions as $aggregateFunction) {
                $this->aggregateFunctions[$aggregateFunction->print()] = $aggregateFunction;
            }
        }

        if ($havingCondition->getRight() instanceof IMathBinaryOperator) {
            $aggregateFunctions = $this->findAggregateFunctionsInMathExpressions($havingCondition->getRight());

            foreach ($aggregateFunctions as $aggregateFunction) {
                $this->aggregateFunctions[$aggregateFunction->print()] = $aggregateFunction;
            }
        }

        return $this;
    }

    public function orderBy(IExpression $expression, $order = 'ASC') : static
    {
        $this->orderByColumns[] = new OrderByExpression($expression, $order === 'ASC');

        return $this;
    }

    public function limit(int $limit) : static
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset) : static
    {
        $this->offset = $offset;

        return $this;
    }

    public function limitOffset(int $limit, int $offset) : static
    {
        $this->limit($limit);
        $this->offset($offset);

        return $this;
    }

    public function intersect(SelectBuilder $select) : static
    {
        $this->intersected[] = $select;

        return $this;
    }

    public function except(SelectBuilder $select) : static
    {
        $this->exceptedQueries[] = $select;

        return $this;
    }

    public function union(SelectBuilder $select) : static
    {
        $this->unionedQueries[] = $select;

        return $this;
    }

    public function unionAll(SelectBuilder $select) : static
    {
        $this->unionedAllQueries[] = $select;

        return $this;
    }

    public function execute() : array
    {
        if ($this->executed) {
            return $this->result;
        }

        $executor = new SelectExecutor($this);

        $this->result = $executor->run();
        $this->executed = true;

        return $this->result;
    }

    public function getResult() : array
    {
        return $this->result;
    }

    public function explain()
    {
        $executor = new ExplainExecutor($this);

        return $executor->run();
    }

    public function printQuery() : string
    {
        $printer = new SelectPrinter($this, 1);

        return $printer->print();
    }

    /**
     * @throws Exception
     * @return string
     */
    public function printResult() : string
    {
        if (!$this->executed) {
            throw new Exception('Query was not executed. Call execute().');
        }

        $tableResult = new TableResult($this);

        return $tableResult->print();
    }

    /// GETTERS

    /**
     * @return OrderByExpression[]
     */
    public function getOrderByColumns(): array
    {
        return $this->orderByColumns;
    }

    /**
     * @return Column[]
     */
    public function getGroupByColumns(): array
    {
        return $this->groupByColumns;
    }

    /**
     * @return ISelect[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * @return ?IFromExpression
     */
    public function getFromClause(): ?IFromExpression
    {
        return $this->fromClause;
    }

    /**
     * @return WhereCondition[]
     */
    public function getWhereConditions(): array
    {
        return $this->whereConditions;
    }

    /**
     * @return JoinExpression[]
     */
    public function getLeftJoinedTables(): array
    {
        return $this->leftJoinedTables;
    }

    /**
     * @return JoinExpression[]
     */
    public function getRightJoinedTables(): array
    {
        return $this->rightJoinedTables;
    }

    /**
     * @return JoinExpression[]
     */
    public function getInnerJoinedTables(): array
    {
        return $this->innerJoinedTables;
    }

    /**
     * @return JoinExpression[]
     */
    public function getCrossJoinedTables(): array
    {
        return $this->crossJoinedTables;
    }

    public function getLimit() : ?int
    {
        return $this->limit;
    }

    public function getOffset() : ?int
    {
        return $this->offset;
    }

    /**
     * @return AggregateFunctionExpression[]
     */
    public function getAggregateFunctions() : array
    {
        return $this->aggregateFunctions;
    }

    /**
     * @return FunctionExpression[]
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @return IValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return IMathBinaryOperator[]
     */
    public function getMathBinaryOperators() : array
    {
        return $this->mathBinaryOperators;
    }

    /**
     * @return HavingCondition[]
     */
    public function getHavingConditions() : array
    {
        return $this->havingConditions;
    }

    /**
     * @return ?IExpression
     */
    public function getDistinct(): ?IExpression
    {
        return $this->distinct;
    }

    /**
     * @return SelectBuilder[]
     */
    public function getIntersected(): array
    {
        return $this->intersected;
    }

    /**
     * @return SelectBuilder[]
     */
    public function getExceptedQueries(): array
    {
        return $this->exceptedQueries;
    }

    public function getUnionedAllQueries() : array
    {
        return $this->unionedAllQueries;
    }

    /**
     * @return SelectBuilder[]
     */
    public function getUnionedQueries(): array
    {
        return $this->unionedQueries;
    }
}