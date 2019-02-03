<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 1. 2. 2019
 * Time: 9:53
 */

/**
 * Class Query
 *
 * @author Tomáš Babický tomas.babicky@websta.de
 */
class Query
{
    const ENABLED_OPERATORS = ['=', '<', '>', '<=', '>=', '!='];

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
    }

    /**
     * Query destructor.
     */
    public function __destruct()
    {
        $this->database       = null;
        $this->columns        = null;
        $this->table          = null;
        $this->whereCondition = null;
        $this->orderBy        = null;
        $this->groupBy        = null;
        $this->leftJoin       = null;
        $this->innerJoin      = null;
        $this->onCondition    = null;
        $this->query          = null;
        $this->limit          = null;
        $this->isSelect       = null;
        $this->isDelete       = null;
        $this->isUpdate       = null;
        $this->isInsert       = null;
        $this->insertData     = null;
        $this->updateData     = null;
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
        $this->columns = $columns;

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
     * @param $column
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

        $this->limit = $limit;

        return $this;
    }

    public function leftJoin($table)
    {
        $this->leftJoin[] = new Table($this->database, $table);
        
        return $this;
    }

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
     */
    public function on($column, $operator, $value)
    {
        $this->onCondition[] = ['column' =>$column, 'operator' => $operator, 'value' => $value];
        
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

    public function add($table, array $data)
    {
        $this->isInsert   = true;
        $this->insertData = $data;
        $this->table      = new Table($this->database, $table);

        return $this;
    }

    public function delete($table)
    {
        $this->isDelete = true;
        $this->table    = new Table($this->database, $table);

        return $this;
    }

    private function generateWhere()
    {
        $where   = '';

        if (count($this->whereCondition)) {
            $where = 'WHERE ';

            foreach ($this->whereCondition as $condition) {
                $where .= sprintf('%s %s %s ', $condition['column'], $condition['operator'], $condition['value']);
            }
        }

        return $where;
    }

    private function generateGroupBy()
    {
        $groupBy = '';

        if (count($this->groupBy)) {
            $groupBy = 'GROUP BY ';

            foreach ($this->groupBy as $value) {
                $groupBy .= $value;
            }
        }

        return $groupBy;
    }

    private function generateOrderBy()
    {
        $orderBy = '';

        if (count($this->orderBy)) {
            $orderBy = 'ORDER BY ';

            foreach ($this->orderBy as $values) {
                $type = $values['asc'] ? 'ASC' : 'DESC';

                $orderBy .= sprintf('%s %s', $values['column'], $type);
            }
        }

        return $orderBy;
    }

    /**
     * @return string
     */
    private function generateLimit()
    {
        $limit = '';

        if ($this->limit) {
            $limit = 'LIMIT ' . $this->limit;
        }

        return $limit;
    }

    /**
     * @return string
     */
    private function build()
    {
        if ($this->isSelect) {
            $select = sprintf('SELECT %s FROM %s ', implode(', ', $this->columns), $this->table->getName());

            $where   = $this->generateWhere();
            $orderBy = $this->generateOrderBy();
            $groupBy = $this->generateGroupBy();
            $limit   = $this->generateLimit();

            return sprintf('%s %s %s %s %s', $select, $where, $groupBy, $orderBy, $limit);
        }

        if ($this->isDelete) {
            $where   = $this->generateWhere();
            $limit   = $this->generateLimit();

            return sprintf('DELETE FROM %s %s %s', $this->table->getName(), $where, $limit);
        }

        if ($this->isUpdate) {
            $where   = $this->generateWhere();
            $limit   = $this->generateLimit();
            $set     = '';

            if ($this->updateData) {
                $set = 'SET ';

                foreach ($this->updateData as $column => $value) {
                    $set .= sprintf('%s = %s', $column, $value);
                }
            }

            return sprintf('UPDATE %s %s %s %s', $this->table->getName(), $set, $where, $limit);
        }

        if ($this->isInsert) {
            $columns = array_keys($this->insertData);
            $values  = array_values($this->insertData);

            return sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                $this->table->getName(),
                implode(', ', $columns),
                implode(', ', $values)
            );
        }
    }

    public function show()
    {
        echo sprintf('I have built this query: %s', $this->build());
    }

    /**
     * @return Result
     */
    public function run()
    {
         $startTime = microtime(true);
         
         $columns = [];
         
         /**
          * @var Table $table
          */
         foreach ($this->innerJoin as $table) {
             foreach ($table->getColumns() as $column) {
                 $columns[] = $column->getName();
             }
         }
         
         foreach ($this->leftJoin as $table) {
             foreach ($table->getColumns() as $column) {
                 $columns[] = $column;
             }
         }
         
         foreach ($this->table->getColumns() as $column) {
             $columns[] = $column->getName();
         }
         
         foreach ($this->columns as $column) {
             if (!in_array($column, $columns)) {
                 throw new Exception(sprintf('Selected column "%s" does not exists.', $column));
             }
         }
         
         /*
         foreach ($this->columns as $selectedColumn) {
             if(!$this->table->columnExists($selectedColumn)) {
                 throw new Exception(sprintf('Selected column "%s" does not exists in table "%s".', $selectedColumn, $this->table->getName()));
             }
         }
         */
        
        /**
         * @var Row[] $tmpRows
         */
        $tmpRows = $this->table->getRows();
        $res     = [];            

        if (count($this->whereCondition)) {
            
            /**
             * @var Row $tmpRow
             */
            foreach ($tmpRows as $tmpRow) {
                foreach ($this->whereCondition as $condition) {
                    if ($condition['operator'] === '=') {
                        if ($tmpRow[$condition['column']] === $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }

                    if ($condition['operator'] === '<') {
                        if ($tmpRow[$condition['column']] < $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }

                    if ($condition['operator'] === '>') {
                        if ($tmpRow[$condition['column']] > $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }

                    if ($condition['operator'] === '<=') {
                        if ($tmpRow[$condition['column']] <= $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }

                    if ($condition['operator'] === '>=') {
                        if ($tmpRow[$condition['column']] >= $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }

                    if ($condition['operator'] === '!=') {
                        if ($tmpRow[$condition['column']] !== $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }
                }
            }
        } else {
            $res = $tmpRows;
        }
        
        if (count($this->innerJoin)) {
            if (!count($this->onCondition)) {
                throw new Exception('No ON condition.');
            }
            
            $joinTmp = [];
            
            /**
             * @var Table $joinTable
             */
            foreach ($this->innerJoin as $joinTable) {                
               /* foreach ($joinTable->getColumns() as $joinTableColumns) {
                    $this->columns[] = $joinTableColumns->getName();
                }
                */
                
                foreach ($this->onCondition as $condition) {
                    foreach ($res as $row) {
                        foreach ($row as $column => $value) {                            
                            if ($column === $condition['column']) {
                                foreach ($joinTable->getRows() as $joinedTableRows ) {
                                    foreach ($joinedTableRows as $joinedTableRowsKey => $joinedTableRowsValue) {
                                        if ($joinedTableRowsKey === $condition['value']) {
                                            if ($value === $joinedTableRowsValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            $res = $joinTmp;
        }

        if (count($this->groupBy)) {
            $groups   = [];
            $tmpGroup = [];
            $lostRows = [];
            
            foreach ($res as $row) {
                foreach ($row as $column => $value) {
                    foreach ($this->groupBy as $groupColumn) {
                        if ($column === $groupColumn) {
                            if (isset($groups[$value])) {
                                $groups[$value]['count'] += 1;
                            } else {
                                $groups[$value]['count'] = 1;
                            }
                            
                            $groups[$value]['row'][] = $row;                             
                            $lostRows[]              = $row;
                        }
                    }
                }
            }
            
            foreach ($groups as $group) {
                $tmpGroup[] = $group['row'][0];  
            }
            
            $res = $tmpGroup;
        }

        if (count($this->orderBy)) {
            $tmpSort = [];            
            $tmp     = [];           
            
            foreach ($res as $column => $values) {
                foreach ($values as $key => $value) {
                    $tmp[$column][$key] = $value;
                }
            }
            
            foreach ($this->orderBy as $value) {
                $tmpSort[] = array_column($tmp, $value['column']);
                $tmpSort[] = $value['asc'] ? SORT_ASC : SORT_DESC;
                $tmpSort[] = SORT_REGULAR;
            }
            
            $tmpSort[] = &$tmp;            
            $sortRes   = call_user_func_array('array_multisort', $tmpSort);
            $res       = $tmp;
        }

        if ($this->limit) {
            $rowsCount = count($res);            
            $limit     = $this->limit > $rowsCount ? $rowsCount : $this->limit;            
            $limitRows = [];

            for ($i = 0; $i < $limit; $i++) {
                $limitRows[] = $res[$i];
            }

            $res = $limitRows;
        }
        
        $columnObj = [];        
        
        foreach ($res as $row) {    
            $newRow = new Row([]);
            
            foreach ($row as $column => $value) {
                if (in_array($column, $this->columns, true)) {
                    $newRow->get()->{$column} = $value;
                }
            }
            $columnObj[] = $newRow;
        }

        $endTime     = microtime(true);
        $executeTime = $endTime - $startTime;

        return new Result($this->columns, $columnObj, $executeTime);
    }
}