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
    const ENABLED_OPERATORS = ['=', '<', '>', '<=', '>=', '!=', '<>'];

    /**
     * @var Database $database
     */
    private $database;

    /**
     * @var array $columns
     */
    private $columns;

    /**
     * @var Table $table
     */
    private $table;

    /**
     * @var array $condition
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
     * @var Table[] $leftJoin
     */
    private $leftJoin;

    /**
     * 
     * @var Table[] $innerJoin
     */
    private $innerJoin;
    
    /**
     * 
     * @var array $onCondition
     */
    private $onCondition;

    /**
     * @var string $query
     */
    private $query;

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
        
        $this->onCondition    = [];
        $this->whereCondition = [];
        
        $this->updateData = [];
        $this->insertData = [];
        
        $this->having = [];
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
        
        $this->leftJoin    = null;
        $this->innerJoin   = null;
        $this->onCondition = null;
        
        $this->limit = null;
        
        $this->query = null;
        
        $this->isSelect = null;
        $this->isDelete = null;
        $this->isUpdate = null;
        $this->isInsert = null;
        
        $this->insertData = null;
        $this->updateData = null;
    }

    /**
     * @return array
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
     * @return array|Table[]
     */
    public function getInnerJoin()
    {
        return $this->innerJoin;
    }

    /**
     * @return array|Table[]
     */
    public function getLeftJoin()
    {
        return $this->leftJoin;
    }

    /**
     * @return array
     */
    public function getOnCondition()
    {
        return $this->onCondition;
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
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
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
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @return Query
     * @throws Exception
     */
    public function where($column, $operator, $value)
    {
        if (!$this->table->columnExists($column)) {
            throw new Exception(sprintf('Column "%s" does not exist.', $column));
        }

        if (!in_array($operator, self::ENABLED_OPERATORS, true)) {
            throw  new Exception(sprintf('Unknown operator "%s".', $column));
        }

        $this->whereCondition[] = ['column' => $column, 'operator' => $operator, 'value' => $value];

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
     * @throws Exception
     */
    public function having($column, $operator, $value)
    {
        if (!$this->table->columnExists($column)) {
            throw new Exception(sprintf('Column "%s" does not exist.', $column));
        }

        if (!in_array($operator, self::ENABLED_OPERATORS, true)) {
            throw  new Exception(sprintf('Unknown operator "%s".', $column));
        }

        $this->having[] = [
          'column'   => $column,
          'operator' => $operator,
          'value'    => $value
        ];
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
            throw  new Exception('Limit is not a number.');
        }
        
        if (!$limit) {
            throw new Exception('Zero limit does not make sence.');
        }
        
        if ($limit < 0) {
            throw new Exception('Negative limit does not make sence.');
        }

        $this->limit = $limit;

        return $this;
    }

    /**
     * @param string $table
     *
     * @return Query
     */
    public function leftJoin($table)
    {
        $this->leftJoin[] = new Table($this->database, $table);
        
        return $this;
    }

    /**
     * @param string $table
     * 
     * @return Query
     */
    public function innerJoin($table)
    {
        $this->innerJoin[] = new Table($this->database, $table);
        
        return $this;
    }

    /**
     *
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @return Query
     * @throws Exception
     */
    public function on($column, $operator, $value)
    {
        /**
         * 
         * @var Table $last
         */
        if (isset($this->innerJoin[count($this->innerJoin) - 1])) {
            $last = $this->innerJoin[count($this->innerJoin) - 1];
            
            if (!$last) {
                throw new Exception('ON condition has no join.');
            }
        } else {
            if (isset($this->leftJoin[count($this->leftJoin) - 1])) {
                $last = $this->leftJoin[count($this->leftJoin) - 1];
                
                if (!$last) {
                    throw new Exception('ON condition has no join.');
                }
            } else {
                throw new Exception('ON condition has no join.');
            }
        }
        
        $this->onCondition[] = [
            'column'   => $column,
            'operator' => $operator,
            'value'    => $value,
            'table'    => $last->getName()
        ];
        
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
         $startTime = microtime(true);
         
         if ($this->isSelect) {
             $select      = new Select($this);
             $columnObj   = $select->run();
             $endTime     = microtime(true);
             $executeTime = $endTime - $startTime;
             
             return new Result($this->columns, $columnObj, $executeTime);
         }

         if ($this->isInsert) {
             $insert       = new Insert($this);
             $affectedRows = $insert->run();
             $endTime      = microtime(true);
             $executeTime  = $endTime - $startTime;

             return new Result([], [], $executeTime, $affectedRows);
         }

         if ($this->isUpdate) {
             $update       = new Update($this);
             $affectedRows = $update->run();
             $endTime      = microtime(true);
             $executeTime  = $endTime - $startTime;

             return new Result([], [], $executeTime, $affectedRows);
         }

         if ($this->isDelete) {
             $delete       = new Delete($this);
             $affectedRows = $delete->run();
             $endTime      = microtime(true);
             $executeTime  = $endTime - $startTime;

             return new Result([], [], $executeTime, $affectedRows);
         }
    }
}