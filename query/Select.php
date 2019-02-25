<?php
namespace query;

use Query;
use Row;
use Table;
use Exception;

class Select
{
    /**
     * @var Query $query
     */
    private $query;
    /**
     * @var array|Row[] $result
     */
    private $result;

    /**
     * Select constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query  = $query;
        $this->result = $query->getTable()->getRows();
    }

    /**
     * Select destructor.
     */
    public function __destruct()
    {
        $this->result = null;
        $this->query  = null;
    }

    /**
     * @return array|Row[]
     */
    public function run()
    {
        $this->checkColumns();
        
        $this->innerJoin();
        $this->leftJoin();
        $this->where();
        $this->groupBy();
        $this->having();
        $this->orderBy();
        $this->limit();
        $this->functions();
        
        return $this->createRows();
    }

    /**
     * @return void
     * @throws Exception
     */
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
                $columns[] = $column->getName();
            }
        }
        
        foreach ($this->query->getTable()->getColumns() as $column) {
            $columns[] = $column->getName();
        }
        
        foreach ($this->query->getColumns() as $column) {
            if (!in_array($column, $columns, true)) {
                throw new Exception(sprintf('Selected column "%s" does not exists.', $column));
            }
        }
    }

    /**
     *
     */
    private function functions()
    {
        $functions = new Functions($this->result);

        foreach ($this->query->getFunctions() as $function) {
            if ($function['function'] === 'sum') {
                array_merge($this->result, $functions->sum($function['column']));
            }

            if ($function['function'] === 'count') {
                array_merge($this->result, $functions->count($function['column']));
            }

            if ($function['function'] === 'avg') {
                array_merge($this->result, $functions->avg($function['column']));
            }

            if ($function['function'] === 'min') {
                array_merge($this->result, $functions->min($function['column']));
            }

            if ($function['function'] === 'max') {
                array_merge($this->result, $functions->max($function['column']));
            }

            if ($function['function'] === 'median') {
                array_merge($this->result, $functions->median($function['column']));
            }
        }

        return $this->result;
    }

    /**
     * @return array|Row[]
     * @throws Exception
     */
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

    /**
     * @return array|Row[]
     * @throws Exception
     */
    private function leftJoin()
    {
        if (!count($this->query->getLeftJoin())) {
            return $this->result;
        }
        
        if (!count($this->query->getOnCondition())) {
            throw new Exception('No ON condition.');
        }
        
        $joinTmp = [];
        
        foreach ($this->query->getLeftJoin() as $joinTable) {
            foreach ($this->query->getOnCondition() as $condition) {
                foreach ($this->result as $row) {
                    foreach ($row as $column => $value) {
                        if ($column === $condition['column']) {
                            foreach ($joinTable->getRows() as $joinedTableRows) {
                                foreach ($joinedTableRows as $columnName => $columnValue) {
                                    if ($columnName === $condition['value']) {

                                        if ($condition['operator'] === '=') {
                                            if ($value === $columnValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }

                                        if ($condition['operator'] === '<') {
                                            if ($value < $columnValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }

                                        if ($condition['operator'] === '>') {
                                            if ($value > $columnValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }

                                        if ($condition['operator'] === '>=') {
                                            if ($value >= $columnValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }

                                        if ($condition['operator'] === '<=') {
                                            if ($value <= $columnValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }

                                        if ($condition['operator'] === '!=') {
                                            if ($value !== $columnValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }
                                    } else {                                        
                                        foreach ($joinTable->getColumns() as $joinColumn) {
                                            $row[$joinColumn->getName()] = null;
                                        }
                                   
                                        $joinTmp[$columnName] = $row;
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

    /**
     * @return array|Row[]
     */
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
                    
                if ($condition['operator'] === '!=' || $condition['operator'] === '<>') {
                    if ($tmpRow[$condition['column']] !== $condition['value']) {
                        $res[] = $tmpRow;
                    }
                }
            }
        }

        return $this->result = $res;
    }

    /**
     * @return array|Row[]
     */
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
                foreach ($this->query->getGroupBy() as $groupColumn) {
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

        $this->query->setGrouped($lostRows);
            
        foreach ($groups as $group) {
            $tmpGroup[] = $group['row'][0];
        }
            
        return $this->result = $tmpGroup;
    }

    /**
     * @return array|Row[]
     */
    private function having()
    {
        if (!count($this->query->getGrouped())) {
            return $this->result;
        }

        $tmp = [];

        foreach ($this->query->getHaving() as $having) {
            foreach ($this->query->getGrouped() as $grouped) {
                foreach ($this->result as $row) {
                    foreach ($row as $column => $value) {
                        if ($column === $having['column']) {
                            if ($having['operator'] === '=') {
                                if ($value === $having['value']) {

                                }
                            }

                            if ($having['operator'] === '>') {
                                if ($value > $having['value']) {

                                }
                            }

                            if ($having['operator'] === '<') {
                                if ($value < $having['value']) {

                                }
                            }

                            if ($having['operator'] === '>=') {
                                if ($value >= $having['value']) {

                                }
                            }

                            if ($having['operator'] === '<=') {
                                if ($value <= $having['value']) {

                                }
                            }

                            if ($having['operator'] === '!=' || $having['operator'] === '<>') {
                                if ($value !== $having['value']) {

                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array|Row[]
     */
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

    /**
     * @return array|Row[]
     */
    private function limit()
    {
        if (!$this->query->getLimit()) {
            return $this->result;
        }
        
        $rowsCount = count($this->result);
        $limit     = $this->query->getLimit() > $rowsCount ? $rowsCount : $this->query->getLimit();

        return $this->result = array_slice($this->result,0, $limit,true);
    }

    /**
     * @return array
     */
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

