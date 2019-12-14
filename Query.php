<?php

use query\Delete;
use query\Explain;
use query\Insert;
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
     * @var array $columns
     */
    public $columns;

    /**
     * @var Table $table
     */
    private $table;

    /**
     * @var Condition[] $condition
     */
    private $whereCondition;

    /**
     * @var array $orderBy
     */
    private $orderBy;

    /**
     * @var Condition[] $havingConditions
     */
    private $havingConditions;

    /**
     * @var array $groupBy
     */
    private $groupBy;

    /**
     *
     * @var array $leftJoin
     */
    private $leftJoin;

    /**
     * @var array $rightJoin
     */
    private $rightJoin;

    /**
     * @var array $fullJoin
     */
    private $fullJoin;

    /**
     * @var array $innerJoin
     */
    private $innerJoin;

    /**
     * @var Table[] $crossJoin
     */
    private $crossJoin;

    /**
     * @var int $offset
     */
    private $offset;

    /**
     * @var int $limit
     */
    private $limit;

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
     * @var array $functions
     */
    private $functions;

    /**
     * @var Result $res
     */
    private $res;

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
     * @var Query[] $union
     */
    private $union;

    /**
     * Query constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;

        $this->columns = [];

        $this->innerJoin = [];

        $this->crossJoin = [];

        $this->leftJoin  = [];
        $this->rightJoin = [];

        $this->fullJoin  = [];

        $this->whereCondition = [];

        $this->orderBy = [];

        $this->groupBy = [];

        $this->havingConditions = [];

        $this->offset = 0;

        $this->updateData = [];
        $this->insertData = [];

        $this->functions = [];

        $this->timeLimit = ini_get('max_execution_time');
        set_time_limit(0);
    }

    /**
     * Query destructor.
     */
    public function __destruct()
    {
        $this->database = null;

        $this->columns = null;

        $this->table = null;

        $this->innerJoin = null;

        $this->crossJoin = null;

        $this->leftJoin  = null;
        $this->rightJoin = null;

        $this->fullJoin = null;

        $this->whereCondition = null;

        $this->groupBy = null;

        $this->havingConditions = null;

        $this->orderBy = null;

        $this->limit = null;

        $this->offset = null;

        $this->type = null;

        $this->insertData = null;
        $this->updateData = null;

        $this->functions = null;

        $this->res = null;

        $this->union = null;

        $this->intersect = null;

        $this->except = null;

        set_time_limit($this->timeLimit);
        $this->timeLimit = null;
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
     * @return array
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * @return Condition[]
     */
    public function getWhereCondition()
    {
        return $this->whereCondition;
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
     * @return array
     */
    public function getInnerJoin()
    {
        return $this->innerJoin;
    }

    /**
     * @return array
     */
    public function getLeftJoin()
    {
        return $this->leftJoin;
    }

    /**
     * @return array
     */
    public function getRightJoin()
    {
        return $this->rightJoin;
    }

    /**
     * @return array
     */
    public function getFullJoin()
    {
        return $this->fullJoin;
    }

    /**
     * @return array
     */
    public function getCrossJoin()
    {
        return $this->crossJoin;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
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
     * @param string $column
     *
     * @return Query
     */
    public function count($column)
    {
        $this->functions[] = ['column' => $column, 'function' => 'count'];

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
        $this->functions[] = ['column' => $column, 'function' => 'sum'];

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
        $this->functions[] = ['column' => $column, 'function' => 'avg'];

        $this->type =self::SELECT;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Query
     */
    public function min($column)
    {
        $this->functions[] = ['column' => $column, 'function' => 'min'];

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
        $this->functions[] = ['column' => $column, 'function' => 'max'];

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
        $this->functions[] = ['column' => $column, 'function' => 'median'];

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
        /*
        foreach ($columns as $column) {
            if (!$this->table->columnExists($column)) {
                throw new Exception(sprintf('Column "%s" does not exist.', $column));
            }
        }
        */

        $this->type = self::SELECT;
        $this->columns  = $columns;

        return $this;
    }

    /**
     * @param string $table
     *
     * @return Query
     */
    public function from($table)
    {
        $this->table = new Table($this->database, $table);

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
        /*
        if (!$this->table->columnExists($column)) {
            throw new Exception(sprintf('Column "%s" does not exist.', $column));
        }*/

        if ($condition->getOperator() === Operator::BETWEEN || $condition->getOperator() === Operator::BETWEEN_INCLUSIVE) {
            if (!is_array($condition->getValue()) && !is_array($condition->getColumn())) {
                throw new Exception('Parameter for between must be array.');
            }

            if (count($condition->getValue()) !== 2 && count($condition->getColumn()) !== 2) {
                throw new Exception('I need two parameters.');
            }
        }

        $this->whereCondition[] = $condition;

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
        /*
        if (!$this->table->columnExists($column)) {
            throw new Exception(sprintf('Column "%s" does not exist.', $column));
        }
        */

        $this->orderBy[] = new OrderBy($column, $asc);

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
        /*
        if (!$this->table->columnExists($column)) {
            throw new Exception(sprintf('Column "%s" does not exist.', $column));
        }
        */

        $this->groupBy[] = $column;

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
        /*
        if (!$this->table->columnExists($column)) {
            throw new Exception(sprintf('Column "%s" does not exist.', $column));
        }
        */

        $this->havingConditions[] = new Condition($column, $operator, $value);

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
     * @param string $table
     * @param array $onConditions
     *
     * @return Query
     * @throws Exception
     */
    public function leftJoin($table, array $onConditions)
    {
        if (!count($onConditions)) {
            throw new Exception('No ON condition.');
        }

        foreach ($onConditions as $onCondition) {
            if (!($onCondition instanceof Condition)) {
                throw new Exception('Given param is not Condition');
            }
        }

        $this->leftJoin[] = ['table' => new Table($this->database, $table), 'onConditions' => $onConditions];
        
        return $this;
    }

    /**
     * @param string $table
     * @param array  $onConditions
     *
     * @return $this
     * @throws Exception
     */
    public function fullJoin($table, array $onConditions)
    {
        if (!count($onConditions)) {
            throw new Exception('No ON condition.');
        }

        foreach ($onConditions as $onCondition) {
            if (!($onCondition instanceof Condition)) {
                throw new Exception('Given param is not Condition');
            }
        }

        $this->fullJoin[] = ['table' => new Table($this->database, $table), 'onConditions' => $onConditions];

        return $this;
    }

    /**
     * @param string $table
     * @param array $onConditions
     *
     * @return Query
     * @throws Exception
     */
    public function rightJoin($table, array $onConditions)
    {
        if (!count($onConditions)) {
            throw new Exception('No ON condition.');
        }

        foreach ($onConditions as $onCondition) {
            if (!($onCondition instanceof Condition)) {
                throw new Exception('Given param is not Condition');
            }
        }

        $this->rightJoin[] = ['table' => new Table($this->database, $table), 'onConditions' => $onConditions];

        return $this;
    }

    /**
     * @param string $table
     * @param Condition[] $onConditions
     *
     * @return Query
     * @throws Exception
     */
    public function innerJoin($table, array $onConditions)
    {
        if (!count($onConditions)) {
            throw new Exception('No ON condition.');
        }

        foreach ($onConditions as $onCondition) {
            if (!($onCondition instanceof Condition)) {
                throw new Exception('Given param is not Condition');
            }
        }

        $this->innerJoin[] = ['table' => new Table($this->database, $table), 'onConditions' => $onConditions];
        
        return $this;
    }

    /**
     * @param string $table
     *
     * @return Query
     */
    public function crossJoin($table)
    {
        $this->crossJoin[] = new Table($this->database, $table);

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
    public function union(Query $query)
    {
         $this->union[] = $query;

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
    public function add($table, array $data)
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
    
    private function proceed()
    {
        
    }
    
    public function execute()
    {
        return new FakeTable([], []);
    }

    /**
     * @return Result
     * @throws Exception
     */
    public function run()
    {
        if ($this->res instanceof Result) {
            return $this->res;
        }

         $startTime = microtime(true);

        switch ($this->type) {
            case self::SELECT:
                $select      = new Select($this);
                $columnObj   = $select->run();
                $endTime     = microtime(true);
                $executeTime = $endTime - $startTime;

                return $this->res = new Result($this->columns, $columnObj, $executeTime);
            case self::INSERT:
                $insert       = new Insert($this);
                $affectedRows = $insert->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->res = new Result([], [], $executeTime, $affectedRows);
            case self::UPDATE:
                $update       = new Update($this);
                $affectedRows = $update->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->res = new Result([], [], $executeTime, $affectedRows);
            case self::DELETE:
                $delete       = new Delete($this);
                $affectedRows = $delete->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->res = new Result([], [], $executeTime, $affectedRows);
            case self::EXPLAIN:
                $explain = new Explain($this);

                $affectedRows = $explain->run();
                $endTime      = microtime(true);
                $executeTime  = $endTime - $startTime;

                return $this->res = new Result([], [], $executeTime, $affectedRows);
            default:
                throw new Exception('Unknown query type.');
        }
    }
}