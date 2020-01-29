<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 1. 2. 2019
 * Time: 9:53
 */

namespace pql;

use Exception;
use Netpromotion\Profiler\Profiler;
use pql\QueryExecute\Delete;
use pql\QueryExecute\DeleteSelect;
use pql\QueryExecute\Explain;
use pql\QueryExecute\Insert;
use pql\QueryExecute\InsertSelect;
use pql\QueryExecute\Select;
use pql\QueryExecute\Update;
use pql\QueryExecute\UpdateSelect;
use pql\QueryPrinter\QueryPrinter;
use pql\QueryResult\IResult;
use pql\QueryResult\ListResult;
use pql\QueryResult\TableResult;

/**
 * Class Query
 *
 * @author rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class Query
{
    /**
     * @var string
     */
    const SELECT = 'select';

    /**
     * @var string
     */
    const INSERT = 'insert';

    /**
     * @var string
     */
    const UPDATE = 'update';

    /**
     * @var string
     */
    const DELETE = 'delete';

    /**
     * @var string
     */
    const INSERT_SELECT = 'insert_select';

    /**
     * @var string
     */
    const EXPLAIN = 'explain';

    /**
     * @var string
     */
    const DELETE_SELECT = 'delete_select';

    /**
     * @var string
     */
    const UPDATE_SELECT = 'update_select';

    /**
     * @var Database $database
     */
    private $database;

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
     * @var AggregateFunction[] $functions
     */
    private $functions;

    /**
     * @var Table|Query $table
     */
    private $table;

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
     * @var Condition[] $condition
     */
    private $whereConditions;

    /**
     * @var bool $hasWhereCondition
     */
    private $hasWhereCondition;

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
     * @var int $limit
     */
    private $limit;

    /**
     * @var int $offset
     */
    private $offset;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var array $updateData
     */
    private $updateData;

    /**
     * @var array|Query $insertData
     */
    private $insertData;

    /**
     * @var Query $deleteData
     */
    private $deleteData;

    /**
     * @var IResult $result
     */
    private $result;

    /**
     * @var string $timeLimit
     */
    private $timeLimit;

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
     * @var bool
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
     * Query constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;

        $this->selectedColumns = [];
        $this->functions = [];

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

        $this->orderByColumns    = [];
        $this->hasOrderBy = false;

        $this->groupByColumns    = [];
        $this->hasGroupBy = false;

        $this->havingConditions   = [];
        $this->hasHavingCondition = false;

        $this->offset = 0;

        $this->updateData = [];
        $this->insertData = [];

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
     * Query destructor.
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

        $this->functions = null;

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

        $this->orderByColumns    = null;
        $this->hasOrderBy = null;

        $this->limit = null;

        $this->offset = null;

        $this->type = null;

        $this->insertData = null;
        $this->updateData = null;
        $this->deleteData = null;

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

    /**
     * prints query in SQL
     *
     * @return string
     */
    public function __toString()
    {
        $queryPrinter = new QueryPrinter($this);

        return $queryPrinter->printQuery();
    }

    /**
     * @return Query
     */
    public function explain()
    {
        $this->type = self::EXPLAIN;

        return $this;
    }

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
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * @return Condition[]
     */
    public function getWhereConditions()
    {
        return $this->whereConditions;
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
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
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
     * @return array|Query
     */
    public function getInsertData()
    {
        return $this->insertData;
    }
    
    /**
     * @return array|Query
     */
    public function getUpdateData()
    {
        return $this->updateData;
    }

    /**
     * @return Query
     */
    public function getDeleteData()
    {
        return $this->deleteData;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return IResult
     */
    public function getResult()
    {
        return $this->result;
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
    public function hasWhereCondition()
    {
        return $this->hasWhereCondition;
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

    /**
     * @param string $column
     *
     * @return Query
     */
    public function count($column)
    {
        $this->functions[] = new AggregateFunction(AggregateFunction::COUNT, [$column]);

        $this->type = self::SELECT;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Query
     */
    public function sum($column)
    {
        $this->functions[] = new AggregateFunction(AggregateFunction::SUM, [$column]);

        //$this->type = self::SELECT;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Query
     */
    public function avg($column)
    {
        $this->functions[] = new AggregateFunction(AggregateFunction::AVERAGE, [$column]);

        $this->type = self::SELECT;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Query
     */
    public function min($column)
    {
        $this->functions[] = new AggregateFunction(AggregateFunction::MIN, [$column]);

        $this->type = self::SELECT;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Query
     */
    public function max($column)
    {
        $this->functions[] = new AggregateFunction(AggregateFunction::MAX, [$column]);

        $this->type = self::SELECT;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Query
     */
    public function median($column)
    {
        $this->functions[] = new AggregateFunction(AggregateFunction::MEDIAN, [$column]);

        $this->type = self::SELECT;

        return $this;
    }

    /**
     * @param array|string $columns
     * @param string|null $alias
     * @return Query
     * @throws Exception
     */
    public function select(array $columns = [], $alias = null)
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
        } elseif (is_array($columns)) {
            $selectedColumns = [];

            foreach ($columns as $column) {
                $selectedColumns[] = new SelectedColumn($column);
            }

            $this->selectedColumns = array_merge($this->selectedColumns, $selectedColumns);
        }

        $this->type = self::SELECT;
        $this->selectedColumnsCount = count($this->selectedColumns);

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Query
     */
    public function distinct($column)
    {
        $this->distinctColumn  = new SelectedColumn($column);
        $this->selectedColumns = [$this->distinctColumn];

        $this->type = self::SELECT;

        return $this;
    }

    /**
     * @param string $table
     * @param string|null $alias
     *
     * @return Query
     * @throws Exception
     */
    public function from($table, $alias = null)
    {
        $this->table = $this->checkTable($table);

        if ($alias) {
            $this->tableAlias = new Alias($this->table, $alias);
            $this->hasTableAlias = true;
        }

        return $this;
    }

    /**
     * @param string|int|AggregateFunction|Query $column
     * @param string $operator
     * @param string|int|AggregateFunction|Query $value
     *
     * @return Query
     * @throws Exception
     */
    public function where($column, $operator, $value)
    {
        $condition = new Condition($column, $operator, $value);

        if ($condition->getOperator() === Operator::BETWEEN ||
            $condition->getOperator() === Operator::BETWEEN_INCLUSIVE
        ) {
            if (!is_array($condition->getValue()) && !is_array($condition->getColumn())) {
                throw new Exception('Parameter for between must be array.');
            }

            if (count($condition->getValue()) !== 2 && count($condition->getColumn()) !== 2) {
                throw new Exception('I need two parameters.');
            }
        }

        if ($condition->getOperator() === Operator::EXISTS) {
            if (!($condition->getColumn() instanceof self) && !$condition->getValue() instanceof self) {
                throw new Exception('Parameter for between must be Query.');
            }
        }

        $this->whereConditions[] = $condition;
        $this->hasWhereCondition = true;

        return $this;
    }

    /**
     * @param string $column
     * @param bool   $asc
     *
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * @param int $limit
     *
     * @return Query
     * @throws Exception
     */
    public function limit($limit)
    {
        if (!is_numeric($limit)) {
            throw new Exception('Limit is not a number.');
        }
        
        if ($limit === 0) {
            throw new Exception('Zero limit does not make sense.');
        }
        
        if ($limit < 0) {
            throw new Exception('Negative limit does not make sense.');
        }

        $this->limit = $limit;

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return Query
     * @throws Exception
     */
    public function offset($offset)
    {
        if (!is_numeric($offset)) {
            throw new Exception('Offset is not a number.');
        }

        if ($offset === 0) {
            throw new Exception('Zero offset does not make sense.');
        }

        if ($offset < 0) {
            throw new Exception('Negative offset does not make sense.');
        }

        $this->offset = $offset;

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
        } elseif ($table instanceof self) {
            if ($table->type !== self::SELECT) {
                throw new Exception('It is not a SELECT query.');
            }

            $originTable   = $table;
            $iteratedTable = $table;

            // find Table
            while ($iteratedTable instanceof self) {
                $iteratedTable = $iteratedTable->getTable();
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
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * @return Query
     */
    public function intersect(Query $query)
    {
        $this->intersectQueries[] = $query;
        $this->hasIntersectQuery  = true;
        return $query;
    }

    /**
     * @param Query $query
     *
     * @return Query
     * @throws Exception
     */
    public function union(Query $query)
    {
        if ($query->getType() !== self::SELECT) {
            throw new Exception('Unioned query is not select query.');
        }

        $this->unionQueries[] = $query;
        $this->hasUnionQuery  = true;

        return $this;
    }

    /**
     * @param Query $query
     *
     * @return Query
     * @throws Exception
     */
    public function unionAll(Query $query)
    {
        if ($query->getType() !== self::SELECT) {
            throw new Exception('Unioned query is not select query.');
        }

        $this->unionAllQueries[] = $query;
        $this->hasUnionAllQuery  = true;

        return $this;
    }

    /**
     * @param string $table
     * @param array  $data
     *
     * @return Query
     *
     */
    public function update($table, array $data)
    {
        $this->type       = self::UPDATE;
        $this->updateData = $data;
        $this->table      = new Table($this->database, $table);

        return $this;
    }

    /**
     * @param string $table
     * @param array  $data
     *
     * @return Query
     * @throws Exception
     */
    public function insert($table, array $data)
    {
        $this->type  = self::INSERT;
        $this->table = new Table($this->database, $table);

        $this->insertData = $data;
        
        $columns = array_keys($data);
        
        foreach ($columns as $column) {
            if (!$this->table->columnExists($column)) {
                $message = sprintf('Column "%s" does not exist in "%s".', $column, $table);

                throw new Exception($message);
            }
        }

        return $this;
    }

    /**
     * @param Query  $select
     * @param string $table
     *
     * @return Query
     */
    public function insertSelect(Query $select, $table)
    {
        $this->type  = self::INSERT_SELECT;
        $this->table = new Table($this->database, $table);

        $this->insertData = $select;

        return $this;
    }

    /**
     * @param Query  $select
     * @param string $table
     *
     * @return Query
     */
    public function deleteSelect(Query $select, $table)
    {
        $this->type  = self::DELETE_SELECT;
        $this->table = new Table($this->database, $table);

        $this->deleteData = $select;

        return $this;
    }

    /**
     * @param Query  $select
     * @param string $table
     *
     * @return Query
     */
    public function updateSelect(Query $select, $table)
    {
        $this->type  = self::UPDATE_SELECT;
        $this->table = new Table($this->database, $table);

        $this->updateData = $select;

        return $this;
    }

    /**
     * @param string $table
     *
     * @return Query
     */
    public function delete($table)
    {
        $this->type  = self::DELETE;
        $this->table = new Table($this->database, $table);

        return $this;
    }

    /**
     * @return IResult
     * @throws Exception
     */
    public function run()
    {
        if ($this->result instanceof TableResult) {
            return $this->result;
        }

        set_time_limit(0);

        $startTime = microtime(true);

        switch ($this->type) {
            case self::SELECT:
                $select      = new Select($this);
                $rows        = $select->run();
                $endTime     = microtime(true);
                $executeTime = $endTime - $startTime;

                return $this->result = new TableResult(
                    array_merge($this->selectedColumns, $select->getColumns()),
                    $rows,
                    $executeTime,
                    $select
                );
            case self::INSERT:
                $insert       = new Insert($this);
                $affectedRows = $insert->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->result = new TableResult([], [], $executeTime, $insert, $affectedRows);
            case self::UPDATE:
                $update       = new Update($this);
                $affectedRows = $update->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->result = new TableResult([], [], $executeTime, $update, $affectedRows);
            case self::DELETE:
                $delete       = new Delete($this);
                $affectedRows = $delete->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->result = new TableResult([], [], $executeTime, $delete, $affectedRows);
            case self::EXPLAIN:
                $explain = new Explain($this);

                $rows = $explain->run();

                $endTime = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->result = new ListResult($rows);
            case self::INSERT_SELECT:
                $insertSelect = new InsertSelect($this);
                $affectedRows = $insertSelect->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->result = new TableResult([], [], $executeTime, $insertSelect, $affectedRows);
            case self::DELETE_SELECT:
                $deleteSelect = new DeleteSelect($this);
                $affectedRows = $deleteSelect->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->result = new TableResult([], [], $executeTime, $deleteSelect, $affectedRows);
            case self::UPDATE_SELECT:
                $updateSelect = new UpdateSelect($this);
                $affectedRows = $updateSelect->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->result = new TableResult([], [], $executeTime, $updateSelect, $affectedRows);
            default:
                $message = sprintf('Unknown query type "%s".', $this->type);

                throw new Exception($message);
        }
    }
}
