<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:47
 */

namespace pql\QueryBuilder;

use Exception;
use Netpromotion\Profiler\Profiler;
use pql\AggregateFunction;
use pql\Alias;
use pql\Condition;
use pql\Database;
use pql\GroupByColumn;
use pql\JoinedTable;
use pql\OrderByColumn;
use pql\QueryBuilder\Select\IExpression;
use pql\QueryExecutor\Select as SelectExecutor;
use pql\QueryResult\IResult;
use pql\QueryResult\TableResult;
use pql\SelectedColumn;
use pql\Table;

/**
 * Class Select
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder
 */
class Select implements IQueryBuilder
{
    use From;
    use Where;
    use Limit;
    use Offset;

    /**
     * @var Database $database
     */
    private $database;

    /**
     * @var IResult $result
     */
    private $result;

    /**
     * @var SelectedColumn[] $selectedColumns
     */
    private $selectedColumns;

    /**
     * @var SelectedColumn $distinctColumn
     */
    private $distinctColumn;

    /**
     * @var int $selectedColumnsCount
     */
    private $selectedColumnsCount;

    /**
     * @var AggregateFunction[] $aggregateFunctions
     */
    private $aggregateFunctions;

    /**
     * @var Alias|null $tableAlias
     */
    private $tableAlias;

    /**
     * @var bool $hasTableAlias
     */
    private $hasTableAlias;

    /**
     * @var JoinedTable[] $innerJoinedTables
     */
    private $innerJoinedTables;

    /**
     * @var bool $hasInnerJoinedTable
     */
    private $hasInnerJoinedTable;

    /**
     * @var JoinedTable[] $leftJoinedTables
     */
    private $leftJoinedTables;

    /**
     * @var bool $hasLeftJoinedTable
     */
    private $hasLeftJoinedTable;

    /**
     * @var JoinedTable[] $rightJoinedTables
     */
    private $rightJoinedTables;

    /**
     * @var bool $hasRightJoinedTable
     */
    private $hasRightJoinedTable;

    /**
     * @var JoinedTable[] $fullJoinedTables
     */
    private $fullJoinedTables;

    /**
     * @var bool $hasFullJoinedTable
     */
    private $hasFullJoinedTable;

    /**
     * @var JoinedTable[] $crossJoinedTables
     */
    private $crossJoinedTables;

    /**
     * @var bool $hasCrossJoinedTable
     */
    private $hasCrossJoinedTable;

    /**
     * @var OrderByColumn[] $orderByColumns
     */
    private $orderByColumns;

    /**
     * @var bool $hasOrderBy
     */
    private $hasOrderBy;

    /**
     * @var Condition[] $havingConditions
     */
    private $havingConditions;

    /**
     * @var bool $hasHavingCondition
     */
    private $hasHavingCondition;

    /**
     * @var GroupByColumn[] $groupByColumns
     */
    private $groupByColumns;

    /**
     * @var bool $hasGroupBy
     */
    private $hasGroupBy;

    /**
     * @var Query[] $unionQueries
     */
    private $unionQueries;

    /**
     * @var bool $hasUnionQuery
     */
    private $hasUnionQuery;

    /**
     * @var Query[] $unionAllQueries
     */
    private $unionAllQueries;

    /**
     * @var bool $hasUnionAllQuery
     */
    private $hasUnionAllQuery;

    /**
     * @var Query[] $intersectQueries
     */
    private $intersectQueries;

    /**
     * @var bool $hasIntersectQuery
     */
    private $hasIntersectQuery;

    /**
     * @var Query[] $exceptQueries
     */
    private $exceptQueries;

    /**
     * @var bool $hasExceptQuery
     */
    private $hasExceptQuery;

    /**
     * @var string $timeLimit
     */
    private $timeLimit;

    /**
     * Select constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;

        $this->selectedColumns = [];
        $this->aggregateFunctions = [];

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

    /**
     * Select destructor.
     */
    public function __destruct()
    {
        Profiler::start('destruct');
        $this->database = null;

        foreach ($this->selectedColumns as &$selectedColumn) {
            $selectedColumn = null;
        }

        unset($selectedColumn);

        $this->selectedColumns      = null;
        $this->selectedColumnsCount = null;

        $this->distinctColumn = null;

        $this->aggregateFunctions = null;

        $this->table = null;

        foreach ($this->innerJoinedTables as &$innerJoinedTable) {
            $innerJoinedTable = null;
        }

        unset($innerJoinedTable);

        $this->innerJoinedTables   = null;
        $this->hasInnerJoinedTable = null;

        foreach ($this->crossJoinedTables as &$crossJoinedTable) {
            $crossJoinedTable = null;
        }

        unset($crossJoinedTable);

        $this->crossJoinedTables   = null;
        $this->hasCrossJoinedTable = null;

        foreach ($this->leftJoinedTables as &$leftJoinedTable) {
            $leftJoinedTable = null;
        }

        unset($leftJoinedTable);

        $this->leftJoinedTables   = null;
        $this->hasLeftJoinedTable = null;

        foreach ($this->rightJoinedTables as &$rightJoinedTable) {
            $rightJoinedTable = null;
        }

        unset($rightJoinedTable);

        $this->rightJoinedTables   = null;
        $this->hasRightJoinedTable = null;

        foreach ($this->fullJoinedTables as &$fullJoinedTable) {
            $fullJoinedTable = null;
        }

        unset($fullJoinedTable);

        $this->fullJoinedTables   = null;
        $this->hasFullJoinedTable = null;

        foreach ($this->whereConditions as &$whereCondition) {
            $whereCondition = null;
        }

        unset($whereCondition);

        $this->whereConditions   = null;
        $this->hasWhereCondition = null;

        foreach ($this->groupByColumns as &$groupByColumn) {
            $groupByColumn = null;
        }

        unset($groupByColumn);

        $this->groupByColumns = null;

        $this->havingConditions   = null;
        $this->hasHavingCondition = null;

        $this->orderByColumns = null;
        $this->hasOrderBy     = null;

        $this->limit = null;

        $this->offset = null;

        $this->result = null;

        foreach ($this->unionQueries as &$unionQuery) {
            $unionQuery = null;
        }

        unset($unionQuery);

        $this->unionQueries  = null;
        $this->hasUnionQuery = null;

        foreach ($this->unionAllQueries as &$unionAllQuery) {
            $unionQuery = null;
        }

        unset($unionAllQuery);

        $this->unionAllQueries  = null;
        $this->hasUnionAllQuery = null;

        foreach ($this->intersectQueries as &$intersectQuery) {
            $intersectQuery = null;
        }

        unset($intersectQuery);

        $this->intersectQueries  = null;
        $this->hasIntersectQuery = null;

        foreach ($this->exceptQueries as &$exceptQuery) {
            $exceptQuery = null;
        }

        unset($exceptQuery);

        $this->exceptQueries  = null;
        $this->hasExceptQuery = null;

        $this->tableAlias = null;

        set_time_limit($this->timeLimit);
        $this->timeLimit = null;

        $this->hasGroupBy = null;

        $this->hasTableAlias = null;

        Profiler::finish('destruct');
    }

    // getters

    // getters

    /**
     * @return Alias|null
     */
    public function getTableAlias()
    {
        return $this->tableAlias;
    }

    /**
     * @return AggregateFunction[]
     */
    public function getAggregateFunctions()
    {
        return $this->aggregateFunctions;
    }

    /**
     * @return GroupByColumn[]
     */
    public function getGroupByColumns()
    {
        return $this->groupByColumns;
    }

    /**
     * @return OrderByColumn[]
     */
    public function getOrderByColumns()
    {
        return $this->orderByColumns;
    }

    /**
     * @return JoinedTable[]
     */
    public function getInnerJoinedTables()
    {
        return $this->innerJoinedTables;
    }

    /**
     * @return JoinedTable[]
     */
    public function getLeftJoinedTables()
    {
        return $this->leftJoinedTables;
    }

    /**
     * @return JoinedTable[]
     */
    public function getRightJoinedTables()
    {
        return $this->rightJoinedTables;
    }

    /**
     * @return JoinedTable[]
     */
    public function getFullJoinedTables()
    {
        return $this->fullJoinedTables;
    }

    /**
     * @return JoinedTable[]
     */
    public function getCrossJoinedTables()
    {
        return $this->crossJoinedTables;
    }

    /**
     * @return Table|Query
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return SelectedColumn[]
     */
    public function getSelectedColumns()
    {
        return $this->selectedColumns;
    }

    /**
     * @return int
     */
    public function getSelectedColumnsCount()
    {
        return $this->selectedColumnsCount;
    }

    /**
     * @return SelectedColumn
     */
    public function getDistinctColumn()
    {
        return $this->distinctColumn;
    }

    /**
     * @return Condition[]
     */
    public function getHavingConditions()
    {
        return $this->havingConditions;
    }

    /**
     * @return Query[]
     */
    public function getUnionQueries()
    {
        return $this->unionQueries;
    }

    /**
     * @return Query[]
     */
    public function getUnionAllQueries()
    {
        return $this->unionAllQueries;
    }

    /**
     * @return Query[]
     */
    public function getIntersectQueries()
    {
        return $this->intersectQueries;
    }

    /**
     * @return Query[]
     */
    public function getExceptQueries()
    {
        return $this->exceptQueries;
    }

    // getters


    // has*

    /**
     * @return bool
     */
    public function hasTableAlias()
    {
        return $this->hasTableAlias;
    }

    /**
     * @return bool
     */
    public function hasInnerJoinedTable()
    {
        return $this->hasInnerJoinedTable;
    }

    /**
     * @return bool
     */
    public function hasLeftJoinedTable()
    {
        return $this->hasLeftJoinedTable;
    }

    /**
     * @return bool
     */
    public function hasRightJoinedTable()
    {
        return $this->hasRightJoinedTable;
    }

    /**
     * @return bool
     */
    public function hasFullJoinedTable()
    {
        return $this->hasFullJoinedTable;
    }

    /**
     * @return bool
     */
    public function hasCrossJoinedTable()
    {
        return $this->hasCrossJoinedTable;
    }

    /**
     * @return bool
     */
    public function hasOrderBy()
    {
        return $this->hasOrderBy;
    }

    /**
     * @return bool
     */
    public function hasGroupBy()
    {
        return $this->hasGroupBy;
    }

    /**
     * @return bool
     */
    public function hasHavingCondition()
    {
        return $this->hasHavingCondition;
    }

    /**
     * @return bool
     */
    public function hasUnionAllQuery()
    {
        return $this->hasUnionAllQuery;
    }

    /**
     * @return bool
     */
    public function hasUnionQuery()
    {
        return $this->hasUnionQuery;
    }

    /**
     * @return bool
     */
    public function hasExceptQuery()
    {
        return $this->hasExceptQuery;
    }

    /**
     * @return bool
     */
    public function hasIntersectQuery()
    {
        return $this->hasIntersectQuery;
    }

    // has*

    public function selectNew(IExpression ...$expressions)
    {
        foreach ($expressions as $expression) {
            $this->selectedColumns[] = new SelectedColumn($expression->evaluate(), $expression);
        }

        $this->selectedColumnsCount = count($this->selectedColumns);

        return $this;
    }


    /**
     * @param array|string $columns
     * @param string|null $alias
     *
     * @return Select
     * @throws Exception
     */
    public function select($columns = [], $alias = null)
    {
        if (is_string($columns)) {
            if (strpos(', ', $columns) === false) {
                if ($alias) {
                    $this->selectedColumns[] = [new SelectedColumn($columns, new Alias($columns, $alias))];
                } else {
                    $this->selectedColumns[] = [new SelectedColumn($columns)];
                }
            } else {
                if ($alias) {
                    throw new Exception('Using alias does not make any sense.');
                }

                $columns = explode(', ', $columns);
                $selectedColumns = [];

                foreach ($columns as $column) {
                    $selectedColumns[] = new SelectedColumn($column);
                }

                $this->selectedColumns = array_merge($this->selectedColumns, $selectedColumns);
            }
        } elseif ($columns instanceof PFunction) {
            $this->selectedColumns[] = new SelectedColumn($columns);
        } elseif (is_array($columns)) {
            $selectedColumns = [];

            foreach ($columns as $column) {
                $selectedColumns[] = new SelectedColumn($column);
            }

            $this->selectedColumns = array_merge($this->selectedColumns, $selectedColumns);
        }

        $this->selectedColumnsCount = count($this->selectedColumns);

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Select
     */
    public function count($column)
    {
        $this->aggregateFunctions[] = new AggregateFunction(AggregateFunction::COUNT, [$column]);

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Select
     */
    public function sum($column)
    {
        $this->aggregateFunctions[] = new AggregateFunction(AggregateFunction::SUM, [$column]);

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Select
     */
    public function avg($column)
    {
        $this->aggregateFunctions[] = new AggregateFunction(AggregateFunction::AVERAGE, [$column]);

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Select
     */
    public function min($column)
    {
        $this->aggregateFunctions[] = new AggregateFunction(AggregateFunction::MIN, [$column]);

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Select
     */
    public function max($column)
    {
        $this->aggregateFunctions[] = new AggregateFunction(AggregateFunction::MAX, [$column]);

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Select
     */
    public function median($column)
    {
        $this->aggregateFunctions[] = new AggregateFunction(AggregateFunction::MEDIAN, [$column]);

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Select
     */
    public function distinct($column)
    {
        $this->distinctColumn  = new SelectedColumn($column);
        $this->selectedColumns = [$this->distinctColumn];

        return $this;
    }

    /**
     * @param string $column
     * @param bool   $asc
     *
     * @return Select
     * @throws Exception
     */
    public function orderBy($column, $asc = true)
    {
        $this->orderByColumns[] = new OrderByColumn($column, $asc);
        $this->hasOrderBy = true;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Select
     * @throws Exception
     *
     */
    public function groupBy($column)
    {
        $this->groupByColumns[]  = new GroupByColumn($column);
        $this->hasGroupBy = true;

        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @return Select
     * @throws Exception
     */
    public function having($column, $operator, $value)
    {
        // COUNT(column_a, column_b)
        $matchedColumn = preg_match('#^([a-zA-Z]*)\((([a-zA-Z0-9,_ ]*)\))$#', $column, $functionNameColumn);
        $matchedValue  = preg_match('#^([a-zA-Z]*)\((([a-zA-Z0-9,_ ]*)\))$#', $value, $functionNameValue);

        if ($matchedColumn) {
            $column = new AggregateFunction($functionNameColumn[1], explode(', ', $functionNameColumn[3]));
        }

        if ($matchedValue) {
            $value = new AggregateFunction($functionNameValue[1], explode(', ', $functionNameValue[3]));
        }

        $this->havingConditions[] = new Condition($column, $operator, $value);
        $this->hasHavingCondition = true;

        return $this;
    }

    /**
     * @param Condition[] $onConditions
     *
     * @throws Exception
     */
    private function checkOnConditions(array $onConditions)
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

    /**
     * @param string|Query $table
     *
     * @return Query|Table
     * @throws Exception
     */
    private function checkTable($table)
    {
        if (is_string($table)) {
            $joinedTable = new Table($this->database, $table);

            if (!$this->database->tableExists($joinedTable)) {
                $message = sprintf(
                    'Selected table "%s" is not from selected database "%s".',
                    $joinedTable->getName(),
                    $this->database->getName()
                );

                throw new Exception($message);
            }
        } elseif ($table instanceof Query) {
            /*
            if ($table->type !== self::SELECT) {
                throw new Exception('It is not a SELECT query.');
            }
            */

            $originTable   = $table;
            $iteratedTable = $table;

            // find Table
            while ($iteratedTable instanceof Query) {
                $iteratedTable = $iteratedTable->getQuery()->getTable();
            }

            if (!$iteratedTable->getDatabase()->tableExists($iteratedTable)) {
                $message = sprintf(
                    'Selected table "%s" is not from selected database "%s".',
                    $table->getTable(),
                    $this->database->getName()
                );

                throw new Exception($message);
            }

            $joinedTable = $originTable;
        } else {
            throw new Exception('Unsupported input.');
        }

        return $joinedTable;
    }

    /**
     * @param string|Query $table
     * @param array        $onConditions
     * @param string|null  $alias
     *
     * @return Select
     * @throws Exception
     */
    public function leftJoin($table, array $onConditions, $alias = null)
    {
        $leftJoinedTable = $this->checkTable($table);

        $this->checkOnConditions($onConditions);

        $this->leftJoinedTables[] = new JoinedTable($leftJoinedTable, $onConditions, $alias);
        $this->hasLeftJoinedTable = true;

        return $this;
    }

    /**
     * @param string|Query $table
     * @param array        $onConditions
     * @param string|null  $alias
     *
     * @return Select
     * @throws Exception
     */
    public function fullJoin($table, array $onConditions, $alias = null)
    {
        $fullJoinedTable = $this->checkTable($table);

        $this->checkOnConditions($onConditions);

        $this->fullJoinedTables[] = new JoinedTable($fullJoinedTable, $onConditions, $alias);
        $this->hasFullJoinedTable = true;

        return $this;
    }

    /**
     * @param string|Query $table
     * @param array        $onConditions
     * @param string|null  $alias
     *
     * @return Select
     * @throws Exception
     */
    public function rightJoin($table, array $onConditions, $alias = null)
    {
        $rightJoinedTable = $this->checkTable($table);

        $this->checkOnConditions($onConditions);

        $this->rightJoinedTables[] = new JoinedTable($rightJoinedTable, $onConditions, $alias);
        $this->hasRightJoinedTable = true;

        return $this;
    }

    /**
     * @param string|Query $table
     * @param Condition[]  $onConditions
     * @param string|null  $alias
     *
     * @return Select
     * @throws Exception
     */
    public function innerJoin($table, array $onConditions = [], $alias = null)
    {
        $joinedTable = $this->checkTable($table);

        if (count($onConditions)) {
            $this->innerJoinedTables[] = new JoinedTable($joinedTable, $onConditions, $alias);
            $this->hasInnerJoinedTable = true;
        } else {
            $this->crossJoinedTables[] = new JoinedTable($joinedTable, [], $alias);
            $this->hasCrossJoinedTable = true;
        }

        return $this;
    }

    /**
     * @param Condition[] $onConditions
     * @param string|null $alias
     *
     * @return Select
     * @throws Exception
     */
    public function selfJoin(array $onConditions = [], $alias = null)
    {
        if (!$this->table) {
            throw new Exception('Cannot do SELF JOIN, if i have empty FROM clause.');
        }

        return $this->innerJoin($this->table, $onConditions, $alias);
    }

    /**
     * @param string|Query $table
     * @param string|null  $alias
     *
     * @return Select
     * @throws Exception
     */
    public function crossJoin($table, $alias = null)
    {
        $crossJoinedTable = $this->checkTable($table);

        $this->crossJoinedTables[] = new JoinedTable($crossJoinedTable, [], $alias);
        $this->hasCrossJoinedTable = true;

        return $this;
    }

    /**
     * @param Query $query
     *
     * @return Select
     */
    public function except(Query $query)
    {
        $this->exceptQueries[] = $query;
        $this->hasExceptQuery  = true;

        return $this;
    }

    /**
     * @param Query $query
     *
     * @return Select
     */
    public function intersect(Query $query)
    {
        $this->intersectQueries[] = $query;
        $this->hasIntersectQuery  = true;

        return $this;
    }

    /**
     * @param Query $query
     *
     * @return Select
     * @throws Exception
     */
    public function union(Query $query)
    {
        if (!($query->getQuery() instanceof self)) {
            throw new Exception('Unioned query is not select query.');
        }

        $this->unionQueries[] = $query;
        $this->hasUnionQuery  = true;

        return $this;
    }

    /**
     * @param Query $query
     *
     * @return Select
     * @throws Exception
     */
    public function unionAll(Query $query)
    {
        if (!($query->getQuery() instanceof self)) {
            throw new Exception('Unioned query is not select query.');
        }

        $this->unionAllQueries[] = $query;
        $this->hasUnionAllQuery  = true;

        return $this;
    }

    public function run()
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
