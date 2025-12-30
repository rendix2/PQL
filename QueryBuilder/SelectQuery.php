<?php

namespace pql\QueryBuilder;

use Exception;
use Netpromotion\Profiler\Profiler;
use pql\Alias;
use pql\Condition;
use pql\Database;
use pql\GroupByColumn;
use pql\JoinedTable;
use pql\OrderByColumn;
use pql\QueryBuilder\From\IFromExpression;
use pql\QueryBuilder\Operator\IOperator;
use pql\QueryBuilder\Select\AggregateFunction;
use pql\QueryBuilder\Select\ISelectExpression;
use pql\QueryBuilder\Select\StandardFunction;
use pql\QueryExecutor\SelectExecutor as SelectExecutor;
use pql\QueryResult\IResult;
use pql\QueryResult\TableResult;
use pql\SelectedColumn;
use pql\Table;


class SelectQuery implements IQueryBuilder
{
    use From;
    use WhereQueryBuilder;
    use LimitQueryBuilder;
    use Offset;

    private Database $database;

    private ?IResult $result;

    /**
     * @var SelectedColumn[] $selectedColumns
     */
    private array $selectedColumns;

    private ?SelectedColumn $distinctColumn;

    private int $selectedColumnsCount;

    private array $aggregateFunctions;

    private bool $hasFunctions;

    private bool $hasAggregateFunctions;

    private ?Alias $tableAlias;

    private bool $hasTableAlias;

    /**
     * @var JoinedTable[] $innerJoinedTables
     */
    private array $innerJoinedTables;

    private bool $hasInnerJoinedTable;

    /**
     * @var JoinedTable[] $leftJoinedTables
     */
    private array $leftJoinedTables;

    private bool $hasLeftJoinedTable;

    /**
     * @var JoinedTable[] $rightJoinedTables
     */
    private array $rightJoinedTables;

    private bool $hasRightJoinedTable;

    /**
     * @var JoinedTable[] $fullJoinedTables
     */
    private array $fullJoinedTables;

    private bool $hasFullJoinedTable;

    /**
     * @var JoinedTable[] $crossJoinedTables
     */
    private array $crossJoinedTables;

    private bool $hasCrossJoinedTable;

    /**
     * @var OrderByColumn[] $orderByColumns
     */
    private array $orderByColumns;

    private bool $hasOrderBy;

    /**
     * @var Condition[] $havingConditions
     */
    private array $havingConditions;

    private bool $hasHavingCondition;

    /**
     * @var GroupByColumn[] $groupByColumns
     */
    private array $groupByColumns;

    private bool $hasGroupBy;

    /**
     * @var Query[] $unionQueries
     */
    private array $unionQueries;

    private bool $hasUnionQuery;

    /**
     * @var Query[] $unionAllQueries
     */
    private array $unionAllQueries;

    private bool $hasUnionAllQuery;

    /**
     * @var Query[] $intersectQueries
     */
    private array $intersectQueries;

    private bool $hasIntersectQuery;

    /**
     * @var Query[] $exceptQueries
     */
    private array $exceptQueries;

    private bool $hasExceptQuery;

    private string $timeLimit;

    /**
     * Select constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->table = null;
        $this->tableAlias = null;

        $this->selectedColumns = [];
        $this->distinctColumn = null;

        $this->limit = 0;
        $this->offset = 0;

        $this->result = null;

        $this->hasFunctions = false;

        $this->aggregateFunctions    = [];
        $this->hasAggregateFunctions = false;

        $this->hasTableAlias = false;

        $this->innerJoinedTables   = [];
        $this->hasInnerJoinedTable = false;

        $this->crossJoinedTables   = [];
        $this->hasCrossJoinedTable = false;

        $this->leftJoinedTables   = [];
        $this->hasLeftJoinedTable = false;

        $this->rightJoinedTables   = [];
        $this->hasRightJoinedTable = false;

        $this->fullJoinedTables   = [];
        $this->hasFullJoinedTable = false;

        $this->whereConditions   = [];
        $this->hasWhereCondition = false;

        $this->orderByColumns = [];
        $this->hasOrderBy     = false;

        $this->groupByColumns = [];
        $this->hasGroupBy     = false;

        $this->havingConditions   = [];
        $this->hasHavingCondition = false;

        $this->offset = 0;

        $this->unionAllQueries  = [];
        $this->hasUnionAllQuery = false;

        $this->unionQueries  = [];
        $this->hasUnionQuery = false;

        $this->intersectQueries  = [];
        $this->hasIntersectQuery = false;

        $this->exceptQueries  = [];
        $this->hasExceptQuery = false;

        $this->timeLimit = ini_get('max_execution_time');
    }

    // getters

    // getters

    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * @return Alias|null
     */
    public function getTableAlias(): ?Alias
    {
        return $this->tableAlias;
    }

    /**
     * @return AggregateFunction[]
     */
    public function getAggregateFunctions(): array
    {
        return $this->aggregateFunctions;
    }

    /**
     * @return GroupByColumn[]
     */
    public function getGroupByColumns(): array
    {
        return $this->groupByColumns;
    }

    /**
     * @return OrderByColumn[]
     */
    public function getOrderByColumns(): array
    {
        return $this->orderByColumns;
    }

    /**
     * @return JoinedTable[]
     */
    public function getInnerJoinedTables(): array
    {
        return $this->innerJoinedTables;
    }

    /**
     * @return JoinedTable[]
     */
    public function getLeftJoinedTables(): array
    {
        return $this->leftJoinedTables;
    }

    /**
     * @return JoinedTable[]
     */
    public function getRightJoinedTables(): array
    {
        return $this->rightJoinedTables;
    }

    /**
     * @return JoinedTable[]
     */
    public function getFullJoinedTables(): array
    {
        return $this->fullJoinedTables;
    }

    /**
     * @return JoinedTable[]
     */
    public function getCrossJoinedTables(): array
    {
        return $this->crossJoinedTables;
    }


    public function getTable(): ?Table
    {
        return $this->table;
    }

    /**
     * @return SelectedColumn[]
     */
    public function getSelectedColumns(): array
    {
        return $this->selectedColumns;
    }

    public function getSelectedColumnsCount(): int
    {
        return $this->selectedColumnsCount;
    }

    public function getDistinctColumn(): ?SelectedColumn
    {
        return $this->distinctColumn;
    }

    /**
     * @return Condition[]
     */
    public function getHavingConditions(): array
    {
        return $this->havingConditions;
    }

    /**
     * @return Query[]
     */
    public function getUnionQueries(): array
    {
        return $this->unionQueries;
    }

    /**
     * @return Query[]
     */
    public function getUnionAllQueries(): array
    {
        return $this->unionAllQueries;
    }

    /**
     * @return Query[]
     */
    public function getIntersectQueries(): array
    {
        return $this->intersectQueries;
    }

    /**
     * @return Query[]
     */
    public function getExceptQueries(): array
    {
        return $this->exceptQueries;
    }

    // getters


    // has*

    public function hasFunctions(): bool
    {
        return $this->hasFunctions;
    }

    public function hasTableAlias(): bool
    {
        return $this->hasTableAlias;
    }

    public function hasAggregateFunctions(): bool
    {
        return $this->hasAggregateFunctions;
    }

    public function hasInnerJoinedTable(): bool
    {
        return $this->hasInnerJoinedTable;
    }

    public function hasLeftJoinedTable(): bool
    {
        return $this->hasLeftJoinedTable;
    }

    public function hasRightJoinedTable(): bool
    {
        return $this->hasRightJoinedTable;
    }

    public function hasFullJoinedTable(): bool
    {
        return $this->hasFullJoinedTable;
    }

    public function hasCrossJoinedTable(): bool
    {
        return $this->hasCrossJoinedTable;
    }

    public function hasOrderBy(): bool
    {
        return $this->hasOrderBy;
    }

    public function hasGroupBy(): bool
    {
        return $this->hasGroupBy;
    }

    public function hasHavingCondition(): bool
    {
        return $this->hasHavingCondition;
    }

    public function hasUnionAllQuery(): bool
    {
        return $this->hasUnionAllQuery;
    }

    public function hasUnionQuery(): bool
    {
        return $this->hasUnionQuery;
    }

    public function hasExceptQuery(): bool
    {
        return $this->hasExceptQuery;
    }

    public function hasIntersectQuery(): bool
    {
        return $this->hasIntersectQuery;
    }

    // has*

    public function select($alias = null, ISelectExpression ...$expressions): SelectQuery
    {
        foreach ($expressions as $expression) {
            $this->selectedColumns[] = new SelectedColumn($expression->evaluate(), $expression, $alias);

            if ($expression instanceof AggregateFunction) {
                $this->aggregateFunctions[] = $expression;
                $this->hasAggregateFunctions = true;
            }

            if ($expression instanceof StandardFunction) {
                $this->hasFunctions = true;
            }
        }

        $this->selectedColumnsCount = count($this->selectedColumns);

        return $this;
    }

    public function distinct(ISelectExpression $column): SelectQuery
    {
        $this->distinctColumn  = new SelectedColumn($column->evaluate(), $column);
        $this->selectedColumns = [$this->distinctColumn];

        return $this;
    }

    public function orderBy(ISelectExpression $column, bool $asc = true): SelectQuery
    {
        $this->orderByColumns[] = new OrderByColumn($column->evaluate(), $asc);
        $this->hasOrderBy = true;

        return $this;
    }

    public function groupBy(ISelectExpression $column): SelectQuery
    {
        $this->groupByColumns[] = new GroupByColumn($column->evaluate());
        $this->hasGroupBy       = true;

        return $this;
    }

    public function having(ISelectExpression $expression, IOperator $operator, ISelectExpression $value): SelectQuery
    {
        $this->havingConditions[] = new Condition($expression, $operator, $value);
        $this->hasHavingCondition = true;
    }

    private function checkOnConditions(array $onConditions): void
    {
        if (!count($onConditions)) {
            throw new Exception('No ON condition.');
        }

        foreach ($onConditions as $onCondition) {
            if (!($onCondition instanceof Condition)) {
                throw new Exception('Given param is not Condition');
            }
        }
    }

    private function checkTable(IFromExpression $expression): Query|Table
    {
        if ($expression instanceof \pql\QueryBuilder\From\TableFromExpression) {
            $joinedTable = new Table($this->database, $expression->evaluate());

            if (!$this->database->tableExists($joinedTable)) {
                $message = sprintf(
                    'Selected table "%s" is not from selected database "%s".',
                    $joinedTable->getName(),
                    $this->database->getName()
                );

                throw new Exception($message);
            }
        } elseif ($expression instanceof \pql\QueryBuilder\From\QueryFromExpression) {
            $originTable   = $expression->getQuery();
            $iteratedTable = $expression->getQuery();

            // find Table
            while ($iteratedTable instanceof Query) {
                $iteratedTable = $iteratedTable->getQuery()->getTable();
            }

            if ($expression->getQuery()->getQuery() instanceof self) {
                if (!$expression->getQuery()->getQuery()->getDatabase()->tableExists($iteratedTable)) {
                    $message = sprintf(
                        'Selected table "%s" is not from selected database "%s".',
                        $expression->getQuery()->getQuery()->getTable(),
                        $this->database->getName()
                    );

                    throw new Exception($message);
                }
            }

            $joinedTable = $originTable;
        } else {
            throw new Exception('Unsupported input.');
        }

        return $joinedTable;
    }

    public function leftJoin(IFromExpression $table, array $onConditions, ?string $alias = null): SelectQuery
    {
        $leftJoinedTable = $this->checkTable($table);

        $this->checkOnConditions($onConditions);

        $this->leftJoinedTables[] = new JoinedTable($leftJoinedTable, $onConditions, $alias);
        $this->hasLeftJoinedTable = true;

        return $this;
    }

    public function fullJoin(IFromExpression $table, array $onConditions, ?string $alias = null): SelectQuery
    {
        $fullJoinedTable = $this->checkTable($table);

        $this->checkOnConditions($onConditions);

        $this->fullJoinedTables[] = new JoinedTable($fullJoinedTable, $onConditions, $alias);
        $this->hasFullJoinedTable = true;

        return $this;
    }

    public function rightJoin(IFromExpression $table, array $onConditions, ?string $alias = null): SelectQuery
    {
        $rightJoinedTable = $this->checkTable($table);

        $this->checkOnConditions($onConditions);

        $this->rightJoinedTables[] = new JoinedTable($rightJoinedTable, $onConditions, $alias);
        $this->hasRightJoinedTable = true;

        return $this;
    }

    public function innerJoin(IFromExpression $expression, array $onConditions = [], ?string $alias = null): SelectQuery
    {
        $joinedTable = $this->checkTable($expression);

        if (count($onConditions)) {
            if ($expression instanceof \pql\QueryBuilder\From\TableFromExpression) {
                $this->innerJoinedTables[] = new JoinedTable(new Table($this->database, $joinedTable->getName()), $onConditions, $alias);
            } elseif ($expression instanceof \pql\QueryBuilder\From\QueryFromExpression) {
                $this->innerJoinedTables[] = new JoinedTable($joinedTable, $onConditions, $alias);
            }

            $this->hasInnerJoinedTable = true;
        } else {
            if ($expression instanceof \pql\QueryBuilder\From\TableFromExpression) {
                $this->crossJoinedTables[] = new JoinedTable(new Table($this->database, $joinedTable->getName()), $onConditions, $alias);
            } elseif ($expression instanceof \pql\QueryBuilder\From\QueryFromExpression) {
                $this->crossJoinedTables[] = new JoinedTable($joinedTable, $onConditions, $alias);
            }

            $this->crossJoinedTables[] = new JoinedTable($joinedTable, [], $alias);
            $this->hasCrossJoinedTable = true;
        }

        return $this;
    }

    public function selfJoin(array $onConditions = [], ?string $alias = null): SelectQuery
    {
        if (!$this->table) {
            throw new Exception('Cannot do SELF JOIN, if i have empty FROM clause.');
        }

        return $this->innerJoin(new \pql\QueryBuilder\From\TableFromExpression($this->table->getName()), $onConditions, $alias);
    }

    public function crossJoin(IFromExpression $table, ?string $alias = null): SelectQuery
    {
        $crossJoinedTable = $this->checkTable($table);

        $this->crossJoinedTables[] = new JoinedTable($crossJoinedTable, [], $alias);
        $this->hasCrossJoinedTable = true;

        return $this;
    }

    public function except(Query $query): SelectQuery
    {
        $this->exceptQueries[] = $query;
        $this->hasExceptQuery  = true;

        return $this;
    }

    public function intersect(Query $query): SelectQuery
    {
        $this->intersectQueries[] = $query;
        $this->hasIntersectQuery  = true;

        return $this;
    }

    public function union(Query $query): SelectQuery
    {
        $this->unionQueries[] = $query;
        $this->hasUnionQuery  = true;

        return $this;
    }

    public function unionAll(Query $query): SelectQuery
    {
        $this->unionAllQueries[] = $query;
        $this->hasUnionAllQuery  = true;

        return $this;
    }

    public function run(): IResult
    {
        if ($this->result instanceof TableResult) {
            return $this->result;
        }

        set_time_limit(0);

        $startTime = microtime(true);

        $select      = new SelectExecutor($this);
        $rows        = $select->run();
        $endTime     = microtime(true);
        $executeTime = $endTime - $startTime;

        return $this->result = new TableResult(
            array_merge($this->selectedColumns, $select->getColumns()),
            $rows,
            $executeTime,
            $select
        );
    }
}
