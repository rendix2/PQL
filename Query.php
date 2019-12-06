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
    const ENABLED_OPERATORS = ['=', '<', '>', '<=', '>=', '!=', '<>', 'in', 'between', 'between_in'];

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
     * @var array $functions
     */
    private $functions;

    /**
     * @var Result $res
     */
    private $res;

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

        $this->orderBy = [];

        $this->updateData = [];
        $this->insertData = [];

        $this->having    = [];
        $this->functions = [];

        $this->columns = [];
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

        $this->functions = null;

        $this->res = null;
    }

    /**
     * prints query in SQL
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->isSelect) {
            $select = 'SELECT ' . implode(', ', $this->columns) . '<br>';
            $from = ' FROM ' . $this->table->getName() . '<br>';

            $innerJoin = '';

            if (count($this->innerJoin)) {
                foreach ($this->innerJoin as $table) {
                    $innerJoin .= ' INNER JOIN ' . $table->getName() . '<br>';

                    $i = 0;

                    foreach ($this->onCondition as $condition) {
                        if ($condition['table']->getName() === $table->getName()) {
                            if ($i === 0) {
                                $innerJoin .= ' <br> &nbsp;&nbsp;&nbsp;&nbsp;ON ' . $condition['column'] . ' ' . $condition['operator'] . ' ' . $condition['value'];
                            } else {
                                $innerJoin .= ' <br> &nbsp;&nbsp;&nbsp;&nbsp;AND ' . $condition['column'] . ' ' . $condition['operator'] . ' ' . $condition['value'];
                            }

                            $i++;
                        }
                    }
                }
            }

            $leftJoin = '';

            if (count($this->leftJoin)) {
                foreach ($this->leftJoin as $table) {
                    $leftJoin .= ' LEFT JOIN ' . $table->getName() . '<br>';

                    $i = 0;

                    foreach ($this->onCondition as $condition) {
                        if ($condition['table']->getName() === $table->getName()) {
                            if ($i === 0) {
                                $leftJoin .= ' <br> &nbsp;&nbsp;&nbsp;&nbsp;ON ' . $condition['column'] . ' ' . $condition['operator'] . ' ' . $condition['value'];
                            } else {
                                $leftJoin .= ' <br> &nbsp;&nbsp;&nbsp;&nbsp;AND ' . $condition['column'] . ' ' . $condition['operator'] . ' ' . $condition['value'];
                            }

                            $i++;
                        }
                    }
                }
            }

            $whereCount = count($this->whereCondition);
            $where = '';

            if ($whereCount) {
                $where = ' WHERE ';

                --$whereCount;

                foreach ($this->whereCondition as $i => $whereCondition) {
                    $where .= ' ' . $whereCondition['column'] . ' ' . $whereCondition['operator'] . ' ' . $whereCondition['value'];

                    if ($whereCount !== $i) {
                        $where .= ' <br> &nbsp;&nbsp;&nbsp;&nbsp;AND';
                    }
                }
            }

            $orderBy = '';

            if (count($this->orderBy)) {
                $orderBy = '<br> ORDER BY ';

                foreach ($this->orderBy as $orderedBy) {
                    $orderBy .= $orderedBy['column'] . ' ' . ($orderedBy['asc'] ? 'ASC' : 'DESC');
                }
            }

            $groupBy = '';

            if (count($this->groupBy)) {
                $groupBy = '<br> GROUP BY ';

                foreach ($this->groupBy as $groupedBy) {
                    $groupBy .= $groupedBy . ' ';
                }
            }

            $having = '';

            if (count($this->having)) {
                $having = ' <br> HAVING';

                foreach ($this->having as $havingCondition) {
                    $having.= $havingCondition['column'] . ' ' . $havingCondition['operator'] . ' ' . $havingCondition['value'];
                }
            }

            $limit = '';

            if ($this->limit) {
                $limit = '<br> LIMIT ' . $this->limit;
            }

            return $select . $from . $innerJoin . $leftJoin . $where . $orderBy . $groupBy . $having . $limit;
        }
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return $this->functions;
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
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @return Query
     * @throws Exception
     */
    public function where($column, $operator, $value)
    {
        /*
        if (!$this->table->columnExists($column)) {
            throw new Exception(sprintf('Column "%s" does not exist.', $column));
        }*/

        $operator = mb_strtolower($operator);

        if (!in_array($operator, self::ENABLED_OPERATORS, true)) {
            throw  new Exception(sprintf('Unknown operator "%s".', $operator));
        }

        if ($operator === 'between' || $operator === 'between_in') {
            if (!is_array($value)) {
                throw new Exception('Parameter for between must be array');
            }

            if (count($value) !== 2) {
                throw new Exception('I need two parameters');
            }
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

        if (!in_array($operator, self::ENABLED_OPERATORS, true)) {
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
        $innerKey = count($this->innerJoin) - 1;

        /**
         * 
         * @var Table $last
         */
        if (isset($this->innerJoin[$innerKey])) {
            $last = $this->innerJoin[$innerKey];
            
            if (!$last) {
                throw new Exception('ON condition has no join.');
            }
        } else {
            $leftKey = count($this->leftJoin) - 1;

            if (isset($this->leftJoin[$leftKey])) {
                $last = $this->leftJoin[$leftKey];

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