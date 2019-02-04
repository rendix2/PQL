<?php
namespace query;

use Row;

class Select
{
    private $query;
    
    private $result;
    
    public function __construct(\Query $query) 
    {
        $this->query  = $query;
        $this->result = $query->getTable()->getRows();
    }
    
    public function __destruct()
    {
        $this->result = null;
        $this->query  = null;
    }
    
    public function run()
    {
        $this->checkColumns();
        
        $this->where();
        $this->innerJoin();
        $this->leftJoin();
        $this->groupBy();
        $this->orderBy();
        $this->limit();
        
        return $this->createRows();
    }
    
    private function checkColumns()
    {
        $columns = [];
        
        /**
         * @var Table $table
         */
        foreach ($this->query->getInnerJoin() as $table) {
            foreach ($table->getColumns() as $column) {
                $columns[] = $column->getName();
            }
        }
        
        foreach ($this->query->getLeftJoin() as $table) {
            foreach ($table->getColumns() as $column) {
                $columns[] = $column;
            }
        }
        
        foreach ($this->query->getTable()->getColumns() as $column) {
            $columns[] = $column->getName();
        }
        
        foreach ($this->query->getColumns() as $column) {
            if (!in_array($column, $columns)) {
                throw new Exception(sprintf('Selected column "%s" does not exists.', $column));
            }
        }
        
    }
    
    private function innerJoin()
    {
        if (!count($this->query->getInnerJoin())) {
            return $this->result;
        }
        
        if (!count($this->query->getOnCondition())) {
            throw new Exception('No ON condition.');
        }
            
        $joinTmp = [];
            
        /**
         * @var Table $joinTable
         */
        foreach ($this->query->getInnerJoin() as $joinTable) {
            /* foreach ($joinTable->getColumns() as $joinTableColumns) {
            $this->columns[] = $joinTableColumns->getName();
            }
            */
                
            foreach ($this->query->getOnCondition() as $condition) {
                foreach ($this->result as $row) {
                    foreach ($row as $column => $value) {
                        if ($column === $condition['column'] && $joinTable->getName() === $condition['table']) {
                            foreach ($joinTable->getRows() as $joinedTableRows ) {
                                foreach ($joinedTableRows as $joinedTableRowsKey => $joinedTableRowsValue) {
                                    if ($joinedTableRowsKey === $condition['value'] ) {
                                            
                                        //parse ON condition
                                            
                                        if ($condition['operator'] === '=') {
                                            if ($value === $joinedTableRowsValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }
                                            
                                        if ($condition['operator'] === '<') {
                                            if ($value < $joinedTableRowsValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }
                                            
                                        if ($condition['operator'] === '>') {
                                            if ($value > $joinedTableRowsValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }
                                            
                                        if ($condition['operator'] === '>=') {
                                            if ($value >= $joinedTableRowsValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }
                                            
                                        if ($condition['operator'] === '<=') {
                                            if ($value <= $joinedTableRowsValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }
                                            
                                        if ($condition['operator'] === '!=') {
                                            if ($value !== $joinedTableRowsValue) {
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
        }
        
        return $this->result = $joinTmp;
    }
    
    private function leftJoin()
    {
        return $this->result;
    }
    
    private function where()
    {
        if (!count($this->query->getWhereCondition())) {
            return $this->result;
        }
        
        $res = [];
            
        /**
         * @var Row $tmpRow
         */
        foreach ($this->result as $tmpRow) {
            foreach ($this->query->getWhereCondition() as $condition) {
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

        return $this->result = $res;
    }
    
    private function groupBy()
    {        
        if (!count($this->query->getGroupBy())) {
            return  $this->result;
        }
        
        $groups   = [];
        $tmpGroup = [];
        $lostRows = [];
            
        foreach ($this->result as $row) {
            foreach ($row as $column => $value) {
                foreach ($this->query->ge as $groupColumn) {
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
            
        return $this->result = $tmpGroup;
    }
    
    private function having()
    {
        return $this->result;
    }
    
    private function orderBy()
    {
        if (!count($this->query->getOrderBy())) {
            return $this->result;
        }

        $tmpSort = [];
        $tmp     = [];
            
        foreach ($this->result as $column => $values) {
            foreach ($values as $key => $value) {
                $tmp[$column][$key] = $value;
            }
        }
            
        foreach ($this->query->getOrderBy() as $value) {
            $tmpSort[] = array_column($tmp, $value['column']);
            $tmpSort[] = $value['asc'] ? SORT_ASC : SORT_DESC;
            $tmpSort[] = SORT_REGULAR;
        }
            
        $tmpSort[] = &$tmp;
        $sortRes   = call_user_func_array('array_multisort', $tmpSort);
            
        return $this->result = $tmp;
    }
  
    private function limit()
    {
        if (!$this->query->getLimit()) {
            return $this->result;
        }
        
        $rowsCount = count($this->result);
        $limit     = $this->query->getLimit() > $rowsCount ? $rowsCount : $this->query->getLimit();
        $limitRows = [];
            
        for ($i = 0; $i < $limit; $i++) {
            $limitRows[] = $this->result[$i];
        }

        return $this->result = $limitRows;
    }   
    
    private function createRows()
    {
        $columnObj = [];
        
        foreach ($this->result as $row) {
            $newRow = new Row([]);
            
            foreach ($row as $column => $value) {
                if (in_array($column, $this->query->getColumns(), true)) {
                    $newRow->get()->{$column} = $value;
                }
            }
            $columnObj[] = $newRow;
        }
        
        return $columnObj;
    }
}

