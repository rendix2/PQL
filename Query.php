<?php

use query\Delete;
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
     *
     * @var array $having
     */
    private $having;

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
     *
     * @var array $innerJoin
     */
    private $innerJoin;

    /**
     * @var Table[] $crossJoin
     */
    private $crossJoin;

    /**
     * @var int $limit
     */
    private $limit;

    /**
     *
     * @var bool $isSelect
     */
    private $isSelect;

    /**
     *
     * @var bool $isInsert
     */
    private $isInsert;

    /**
     *
     * @var bool $isUpdate
     */
    private $isUpdate;

    /**
     *
     * @var bool $isDelete
     */
    private $isDelete;

    /**
     * @var array $updateData
     */
    private $updateData;

    /**
     * @var array $insertData
     */
    private $insertData;

    /**
     * @var array $grouped
     */
    private $grouped;

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
     * Query constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;

        $this->isDelete = false;
        $this->isInsert = false;
        $this->isUpdate = false;
        $this->isSelect = false;

        $this->innerJoin = [];
        $this->leftJoin  = [];
        $this->rightJoin = [];
        $this->crossJoin = [];

        $this->whereCondition = [];

        $this->orderBy = [];

        $this->updateData = [];
        $this->insertData = [];

        $this->having    = [];
        $this->functions = [];

        $this->columns = [];

        $this->timeLimit = ini_get('max_execution_time');
        set_time_limit(0);
    }

    /**
     * Query destructor.
     */
    public function __destruct()
    {
        $this->database = null;
        $this->columns  = null;
        $this->table    = null;

        $this->whereCondition = null;
        $this->having         = null;

        $this->orderBy = null;
        $this->groupBy = null;

        $this->innerJoin = null;

        $this->crossJoin = null;

        $this->leftJoin  = null;
        $this->rightJoin = null;

        $this->limit = null;

        $this->isSelect = null;
        $this->isDelete = null;
        $this->isUpdate = null;
        $this->isInsert = null;

        $this->insertData = null;
        $this->updateData = null;

        $this->functions = null;

        $this->res = null;

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
     * @return array
     */
    public function getOrderBy()
    {
        return $this->orderBy;
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
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * @return array
     */
    public function getGrouped()
    {
        return $this->grouped;
    }

    public function setGrouped(array $grouped)
    {
        $this->grouped = $grouped;
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
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return bool
     */
    public function isSelect()
    {
        return $this->isSelect;
    }

    /**
     * @return bool
     */
    public function isInsert()
    {
        return $this->isInsert;
    }

    /**
     * @return bool
     */
    public function isUpdate()
    {
        return $this->isUpdate;
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return $this->isDelete;
    }

    /**
     * @param string $column
     *
     * @return Query
     */
    public function count($column)
    {
        $this->functions[] = ['column' => $column, 'function' => 'count'];

        $this->isSelect  = true;

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

        $this->isSelect  = true;

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

        $this->isSelect  = true;

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

        //$this->columns[] = 'min';
        $this->isSelect  = true;

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

        $this->isSelect  = true;

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

        $this->isSelect  = true;

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

        $this->isSelect = true;
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

        if (!Operator::isOperatorValid($condition->getOperator())) {
            throw new Exception(sprintf('Unknown operator "%s".', $condition->getOperator()));
        }

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
        if (!$this->table->columnExists($column)) {
            throw new Exception(sprintf('Column "%s" does not exist.', $column));
        }

        $this->orderBy[] = ['column' => $column, 'asc' => $asc];

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
        if (!$this->table->columnExists($column)) {
            throw new Exception(sprintf('Column "%s" does not exist.', $column));
        }

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

        if (!in_array($operator, Operator::ENABLED_OPERATORS, true)) {
            throw  new Exception(sprintf('Unknown operator "%s".', $column));
        }

        $this->having[] = [
          'column'   => $column,
          'operator' => $operator,
          'value'    => $value
        ];

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
        
        if (!$limit) {
            throw new Exception('Zero limit does not make sense.');
        }
        
        if ($limit < 0) {
            throw new Exception('Negative limit does not make sense.');
        }

        $this->limit = $limit;

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
     * @param array $onConditions
     *
     * @return Query
     * @throws Exception
     */
    public function rightJoin($table, array $onConditions)
    {
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
     * @param string $table
     * @param array  $data
     *
     * @return Query
     *
     */
    public function update($table, array $data)
    {
        $this->isUpdate   = true;
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
        $this->isInsert   = true;
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
        $this->isDelete = true;
        $this->table    = new Table($this->database, $table);

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
     */
    public function run()
    {
        if ($this->res instanceof Result) {
            return $this->res;
        }

         $startTime = microtime(true);
         
         if ($this->isSelect) {
             $select      = new Select($this);
             $columnObj   = $select->run();
             $endTime     = microtime(true);
             $executeTime = $endTime - $startTime;
             
             return $this->res = new Result($this->columns, $columnObj, $executeTime);
         }

         if ($this->isInsert) {
             $insert       = new Insert($this);
             $affectedRows = $insert->run();
             $endTime      = microtime(true);
             $executeTime  = $endTime - $startTime;

             return $this->res = new Result([], [], $executeTime, $affectedRows);
         }

         if ($this->isUpdate) {
             $update       = new Update($this);
             $affectedRows = $update->run();
             $endTime      = microtime(true);
             $executeTime  = $endTime - $startTime;

             return $this->res = new Result([], [], $executeTime, $affectedRows);
         }

         if ($this->isDelete) {
             $delete       = new Delete($this);
             $affectedRows = $delete->run();
             $endTime      = microtime(true);
             $executeTime  = $endTime - $startTime;

             return $this->res = new Result([], [], $executeTime, $affectedRows);
         }
    }
}