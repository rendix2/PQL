<?php

use Netpromotion\Profiler\Profiler;
use query\Delete;
use query\Explain;
use query\Insert;
use query\InsertSelect;
use query\Select;
use query\Update;

/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 1. 2. 2019
 * Time: 9:53
 */

/**
 * Class Query
 *
 * @author rendix2
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
     * @var Database $database
     */
    private $database;

    /**
     * @var array $selectedColumns
     */
    private $selectedColumns;

    /**
     * @var FunctionPql[] $functions
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
     * @var array $orderBy
     */
    private $orderBy;

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
     * @var array $groupBy
     */
    private $groupBy;

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
     * @var array $insertData
     */
    private $insertData;

    /**
     * @var Result $result
     */
    private $result;

    /**
     * @var string $timeLimit
     */
    private $timeLimit;

    /**
     * @var Query[] $except
     */
    private $except;

    /**
     * @var Query[] $intersect
     */
    private $intersect;

    /**
     * @var Query[] $unionAllQueries
     */
    private $unionAllQueries;

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

        $this->orderBy    = [];
        $this->hasOrderBy = false;

        $this->groupBy    = [];
        $this->hasGroupBy = false;

        $this->havingConditions   = [];
        $this->hasHavingCondition = false;

        $this->offset = 0;

        $this->updateData = [];
        $this->insertData = [];

        $this->unionAllQueries = [];

        $this->intersect = [];

        $this->except = [];

        $this->timeLimit = ini_get('max_execution_time');
    }

    /**
     * Query destructor.
     */
    public function __destruct()
    {
        Profiler::start('destruct');
        $this->database = null;

        $this->selectedColumns = null;
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

        foreach ($this->groupBy as &$groupByColumn) {
            $groupByColumn = null;
        }

        unset($groupByColumn);

        $this->groupBy = null;

        $this->havingConditions   = null;
        $this->hasHavingCondition = null;

        $this->orderBy    = null;
        $this->hasOrderBy = null;

        $this->limit = null;

        $this->offset = null;

        $this->type = null;

        $this->insertData = null;
        $this->updateData = null;

        $this->result = null;

        $this->unionAllQueries = null;

        $this->intersect = null;

        $this->except = null;

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
     * @return FunctionPql[]
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
     * @return array
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @return OrderBy[]
     */
    public function getOrderBy()
    {
        return $this->orderBy;
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
     * @return array
     */
    public function getSelectedColumns()
    {
        return $this->selectedColumns;
    }

    /**
     * @return array
     */
    public function getHavingConditions()
    {
        return $this->havingConditions;
    }

    /**
     * @return array
     */
    public function getInsertData()
    {
        return $this->insertData;
    }
    
    /**
     * @return array
     */
    public function getUpdateData()
    {
        return $this->updateData;
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
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
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
     * @param string $column
     *
     * @return Query
     */
    public function count($column)
    {
        $this->functions[] = new FunctionPql(FunctionPql::COUNT, [$column]);

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
        $this->functions[] = new FunctionPql(FunctionPql::SUM, [$column]);

        $this->type = self::SELECT;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Query
     */
    public function avg($column)
    {
        $this->functions[] = new FunctionPql(FunctionPql::AVERAGE, [$column]);

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
        $this->functions[] = new FunctionPql(FunctionPql::MIN, [$column]);

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
        $this->functions[] = new FunctionPql(FunctionPql::MAX, [$column]);

        $this->type = self::SELECT;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return $this
     */
    public function median($column)
    {
        $this->functions[] = new FunctionPql(FunctionPql::MEDIAN, [$column]);

        $this->type = self::SELECT;

        return $this;
    }

    /**
     * @param array $columns
     *
     * @return Query
     * @throws Exception
     *
     */
    public function select(array $columns = [])
    {
        $this->selectedColumns = $columns;
        $this->type    = self::SELECT;

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
     * @param Condition $condition
     *
     * @return Query
     * @throws Exception
     */
    public function where(Condition $condition)
    {
        if ($condition->getOperator() === Operator::BETWEEN || $condition->getOperator() === Operator::BETWEEN_INCLUSIVE) {
            if (!is_array($condition->getValue()) && !is_array($condition->getColumn())) {
                throw new Exception('Parameter for between must be array.');
            }

            if (count($condition->getValue()) !== 2 && count($condition->getColumn()) !== 2) {
                throw new Exception('I need two parameters.');
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
        $this->orderBy[] = new OrderBy($column, $asc);
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
        $this->groupBy[] = $column;
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
        $matchedValue = preg_match('#^([a-zA-Z]*)\((([a-zA-Z0-9,_ ]*)\))$#', $value, $functionNameValue);

        if ($matchedColumn) {
            $column = new FunctionPql($functionNameColumn[1], explode(', ', $functionNameColumn[3]));
        }

        if ($matchedValue) {
            $value = new FunctionPql($functionNameValue[1], explode(', ', $functionNameValue[3]));
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
        } elseif ($table instanceof self) {
            if ($table->type !== self::SELECT) {
                throw new Exception('Not a select query.');
            }

            $joinedTable = $table;
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
     * @return $this
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
        $this->except[] = $query;

        return $this;
    }

    /**
     * @param Query $query
     *
     * @return Query
     */
    public function intersect(Query $query)
    {
        $this->intersect[] = $query;

        return $query;
    }

    /**
     * @param Query $query
     *
     * @return Query
     */
    public function unionAll(Query $query)
    {
        $this->unionAllQueries[] = $query;

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
        $this->type       = self::INSERT;
        $this->insertData = $data;
        $this->table      = new Table($this->database, $table);
        
        $columns = array_keys($data);
        
        foreach ($columns as $column) {
            if (!$this->table->columnExists($column)) {
                throw new Exception(sprintf('Column "%s" does not exist.', $column));
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
        $this->type       = self::INSERT_SELECT;
        $this->insertData = $select;
        $this->table      = new Table($this->database, $table);

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
     * @return Query[]
     */
    public function getUnionAllQueries()
    {
        return $this->unionAllQueries;
    }

    /**
     * @return Query[]
     */
    public function getIntersect()
    {
        return $this->intersect;
    }

    /**
     * @return Query[]
     */
    public function getExcept()
    {
        return $this->except;
    }

    /**
     * @return Result
     * @throws Exception
     */
    public function run()
    {
        if ($this->result instanceof Result) {
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

                return $this->result = new Result(
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

                return $this->result = new Result([], [], $executeTime, $insert, $affectedRows);
            case self::UPDATE:
                $update       = new Update($this);
                $affectedRows = $update->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->result = new Result([], [], $executeTime, $update, $affectedRows);
            case self::DELETE:
                $delete       = new Delete($this);
                $affectedRows = $delete->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->result = new Result([], [], $executeTime, $delete, $affectedRows);
            case self::EXPLAIN:
                $explain = new Explain($this);

                $columns = ['table', 'rows', 'type', 'condition', 'algorithm'];

                $rows = $explain->run();

                $endTime = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->result = new Result($columns, $rows, $executeTime, $explain, 0);
            case self::INSERT_SELECT:
                $insertSelect = new InsertSelect($this);
                $affectedRows = $insertSelect->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->result = new Result([], [], $executeTime, $insertSelect, $affectedRows);
            default:
                throw new Exception('Unknown query type.');
        }
    }
}