<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Select.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 10:41
 */

namespace PQL\Query\Builder;


use PQL\Database;
use PQL\Query\Builder\Expressions\Column;
use PQL\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Query\Builder\Expressions\FunctionExpression;
use PQL\Query\Builder\Expressions\HavingCondition;
use PQL\Query\Builder\Expressions\IExpression;
use PQL\Query\Builder\Expressions\IFromExpression;
use PQL\Query\Builder\Expressions\ISelect;
use PQL\Query\Builder\Expressions\IValue;
use PQL\Query\Builder\Expressions\WhereCondition;
use PQL\Query\Printer\Select as SelectPrinter;
use PQL\Query\Result\TableResult;
use PQL\Query\Runner\SelectExecutor;
use stdClass;

class Select
{

    private Database $database;

    /**
     * @var ISelect[] $columns
     */
    private array $columns;

    /**
     * @var ?Column $distinct
     */
    private ?Column $distinct;

    /**
     * @var AggregateFunctionExpression[] $aggregateFunctions
     */
    private array $aggregateFunctions;

    /**
     * @var Select[] $exceptedQueries
     */
    private array $exceptedQueries;

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
     * @var Select[] $intersected
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
     * @var Select[] $unionedAllQueries
     */
    private array $unionedAllQueries;

    /**
     * @var Select[] $unionedQueries
     */
    private array $unionedQueries;

    /**
     * @var IValue[] $values
     */
    private array $values;

    /**
     * @var WhereCondition[] $whereConditions
     */
    private array $whereConditions;

    private ?int $limit;

    private ?int $offset;

    /**
     * @var stdClass[] $result
     */
    private array $result;

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

        $this->limit = null;
        $this->offset = null;

        $this->columns = [];
        $this->distinct = null;

        $this->intersected = [];
        $this->exceptedQueries = [];
        $this->unionedAllQueries = [];
        $this->unionedQueries = [];

        $this->result = [];
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

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
            $this->aggregateFunctions[] = $expression;
        }

        if ($expression instanceof FunctionExpression) {
            $this->functions[] = $expression;
        }

        if ($expression instanceof IValue) {
            $this->values[] = $expression;
        }

        return $this;
    }

    public function distinct(Column $column) : static
    {
        $this->distinct = $column;
        $this->columns = [$column];

        return $this;
    }

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

    public function having(HavingCondition $whereCondition) : static
    {
        $this->havingConditions[] = $whereCondition;

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

    public function intersect(Select $select) : static
    {
        $this->intersected[] = $select;

        return $this;
    }

    public function except(Select $select) : static
    {
        $this->exceptedQueries[] = $select;

        return $this;
    }

    public function union(Select $select) : static
    {
        $this->unionedQueries[] = $select;

        return $this;
    }

    public function unionAll(Select $select) : static
    {
        $this->unionedAllQueries[] = $select;

        return $this;
    }

    public function execute() : array
    {
        $executor = new SelectExecutor($this);

        return $this->result = $executor->run();
    }

    public function printQuery() : string
    {
        $printer = new SelectPrinter($this, 1);

        return $printer->print();
    }

    public function printResult() : string
    {
        $tableResult = new TableResult($this, $this->result);

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
     * @return HavingCondition[]
     */
    public function getHavingConditions() : array
    {
        return $this->havingConditions;
    }

    /**
     * @return ?Column
     */
    public function getDistinct(): ?Column
    {
        return $this->distinct;
    }

    /**
     * @return Select[]
     */
    public function getIntersected(): array
    {
        return $this->intersected;
    }

    /**
     * @return Select[]
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
     * @return Select[]
     */
    public function getUnionedQueries(): array
    {
        return $this->unionedQueries;
    }
}