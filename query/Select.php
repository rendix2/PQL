<?php
namespace query;

use Column;
use Exception;
use Query;
use Row;
use Table;

class Select extends BaseQuery
{
    /**
     * Select constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        parent::__construct($query);

        $this->result = $query->getTable()->getRows();
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
        $this->functions();
        $this->having();
        $this->orderBy();
        $this->limit();

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
         * @var Column $column
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
            $function_name = $function['function'];
            $column        = $function['column'];

            $function_column_name = sprintf('%s(%s)', mb_strtoupper($function_name), $column);

            if ($function_name === 'sum') {
                if (count($this->query->getGrouped())) {
                    $this->query->columns[] = $function_column_name;

                    foreach ($this->result as &$row) {
                        $row[$function_column_name] = $row['__group_count'] * $row[$column];
                    }

                    unset($row);
                } else {
                    $sum = $functions->sum($column);

                    if ($this->query->getColumns()) {
                        $this->query->columns[] = $function_column_name;

                        foreach ($this->result as &$row) {
                            $row[$function_column_name] = $sum;
                        }

                        unset($row);
                    } else {
                        $this->query->columns[] = $function_column_name;
                        $this->result           = [0 => [$function_column_name => $sum]];
                    }
                }
            }

            if ($function_name === 'count') {
                if (count($this->query->getGrouped())) {
                    $this->query->columns[] = $function_name;

                    foreach ($this->result as &$row) {
                        $row[$function_name] = $row['__group_count'];
                    }

                    unset($row);
                } else {
                    $count = $functions->count($column);

                    if ($this->query->getColumns()) {
                        $this->query->columns[] = $function_column_name;

                        foreach ($this->result as &$row) {
                            $row[$function_column_name] = $count;
                        }

                        unset($row);
                    } else {
                        $this->query->columns[] = $function_column_name;
                        $this->result           = [0 => [$function_column_name => $count]];
                    }
                }
            }

            if ($function_name === 'avg') {
                $avg= $functions->avg($column);

                if ($this->query->getColumns()) {
                    $this->query->columns[] = $function_column_name;

                    foreach ($this->result as &$row) {
                        $row[$function_column_name] = $avg;
                    }

                    unset($row);
                } else {
                    $this->query->columns[] = $function_column_name;
                    $this->result = [0 => [$function_column_name => $avg]];
                }
            }

            if ($function_name === 'min') {
                $min = $functions->min($column);

                if ($this->query->getColumns()) {
                    $this->query->columns[] = $function_column_name;

                    foreach ($this->result as &$row) {
                        $row[$function_column_name] = $min;
                    }

                    unset($row);
                } else {
                    $this->query->columns[] = $function_column_name;
                    $this->result = [0 => [$function_column_name => $min]];
                }
            }

            if ($function_name === 'max') {
                $max = $functions->max($column);

                if ($this->query->getColumns()) {
                    $this->query->columns[] = $function_column_name;

                    foreach ($this->result as &$row) {
                        $row[$function_column_name] = $max;
                    }

                    unset($row);
                } else {
                    $this->query->columns[] = $function_column_name;
                    $this->result = [0 => [$function_column_name => $max]];
                }
            }

            if ($function_name === 'median') {
                $median = $functions->median($column);

                if ($this->query->getColumns()) {
                    $this->query->columns[] = $function_column_name;

                    foreach ($this->result as &$row) {
                        $row[$function_column_name] = $median;
                    }
                } else {
                    $this->query->columns[] = $function_column_name;
                    $this->result = [0 => [$function_column_name => $median]];
                }
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
                                            
                                        if ($condition['operator'] === '!=' || $condition['operator'] === '<>') {
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

                                        if ($condition['operator'] === '!=' || $condition['operator'] === '<>') {
                                            if ($value !== $columnValue) {
                                                $joinTmp[] = array_merge($row, $joinedTableRows);
                                            }
                                        }
                                    } else {

                                        /**
                                         * @var Column $joinColumn
                                         */
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
                    // if we have SubQuery
                    if ($condition['value'] instanceof Query) {
                        $subQueryValue = $this->runSubQuery($condition);

                        if ($tmpRow[$condition['column']] === $subQueryValue) {
                            $res[] = $tmpRow;
                        }
                    } else {
                        if ($tmpRow[$condition['column']] === $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }
                }
                    
                if ($condition['operator'] === '<') {
                    // if we have SubQuery
                    if ($condition['value'] instanceof Query) {
                        $subQueryValue = $this->runSubQuery($condition);

                        if ($tmpRow[$condition['column']] < $subQueryValue) {
                            $res[] = $tmpRow;
                        }
                    } else {
                        if ($tmpRow[$condition['column']] < $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }
                }
                    
                if ($condition['operator'] === '>') {
                    // if we have SubQuery
                    if ($condition['value'] instanceof Query) {
                        $subQueryValue = $this->runSubQuery($condition);

                        if ($tmpRow[$condition['column']] > $subQueryValue) {
                            $res[] = $tmpRow;
                        }
                    } else {
                        if ($tmpRow[$condition['column']] > $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }
                }

                if ($condition['operator'] === '<=') {
                    // if we have SubQuery
                    if ($condition['value'] instanceof Query) {
                        $subQueryValue = $this->runSubQuery($condition);

                        if ($tmpRow[$condition['column']] <= $subQueryValue) {
                            $res[] = $tmpRow;
                        }
                    } else {
                        if ($tmpRow[$condition['column']] <= $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }
                }
                    
                if ($condition['operator'] === '>=') {
                    // if we have SubQuery
                    if ($condition['value'] instanceof Query) {
                        $subQueryValue = $this->runSubQuery($condition);

                        if ($tmpRow[$condition['column']] >= $subQueryValue) {
                            $res[] = $tmpRow;
                        }
                    } else {
                        if ($tmpRow[$condition['column']] >= $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }
                }
                    
                if ($condition['operator'] === '!=' || $condition['operator'] === '<>') {
                    // if we have SubQuery
                    if ($condition['value'] instanceof Query) {
                        $subQueryValue = $this->runSubQuery($condition);

                        if ($tmpRow[$condition['column']] !== $subQueryValue) {
                            $res[] = $tmpRow;
                        }
                    } else {
                        if ($tmpRow[$condition['column']] !== $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }
                }

                if ($condition['operator'] === 'in') {
                    if ($condition['value'] instanceof Query) {
                        $subQueryValues = $condition['value']->run();

                        if (count($subQueryValues->getColumns()) !== 1) {
                            throw new Exception('Subquery returned more than one column');
                        }

                        foreach ($subQueryValues->getRows() as $subRow) {
                            $col = $subRow->getColumns()[0];

                            if ($subRow->get()->{$col} === $tmpRow[$condition['column']]) {
                                $res[] = $tmpRow;
                            }
                        }
                    } else if (is_array($condition['value'])) {
                        foreach ($condition['value'] as $inValue) {
                            if ($inValue === $tmpRow[$condition['column']]) {
                                $res[] = $tmpRow;
                            }
                        }
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
            
        foreach ($this->result as $row) {
            foreach ($row as $column => $value) {
                foreach ($this->query->getGroupBy() as $groupColumn) {
                    if ($column === $groupColumn) {
                        $groups[$column][$value]['row'] = $row;

                        if (isset($groups[$column][$value]['__group_count'])) {
                            $groups[$column][$value]['__group_count'] += 1;
                        } else {
                            $groups[$column][$value]['__group_count'] = 1;
                        }
                    }
                }
            }
        }

        $this->query->setGrouped($groups);
            
        foreach ($groups as $column => $groupData) {
            foreach ($groupData as $data) {
                $data['row']['__group_count'] = $data['__group_count'];
                unset($data['__group_count']);

                $tmpGroup[] = $data['row'];
            }
        }

        return $this->result = $tmpGroup;
    }

    /**
     * @return array|Row[]
     */
    private function having()
    {
        if (!count($this->query->getHaving())) {
            return $this->result;
        }

        $res = [];

        foreach ($this->query->getHaving() as $having) {
            foreach ($this->result as $row) {
                foreach ($row as $column => $value) {
                    if ($column === $having['column']) {
                        if ($having['operator'] === '=') {
                            if ($value === $having['value']) {
                                $res[] = $row;
                            }
                        }

                        if ($having['operator'] === '>') {
                            if ($value > $having['value']) {
                                $res[] = $row;
                            }
                        }

                        if ($having['operator'] === '<') {
                            if ($value < $having['value']) {
                                $res[] = $row;
                            }
                        }

                        if ($having['operator'] === '>=') {
                            if ($value >= $having['value']) {
                                $res[] = $row;
                            }
                        }

                        if ($having['operator'] === '<=') {
                            if ($value <= $having['value']) {
                                $res[] = $row;
                            }
                        }

                        if ($having['operator'] === '!=' || $having['operator'] === '<>') {
                            if ($value !== $having['value']) {
                                $res[] = $row;
                            }
                        }
                    }
                }
            }
        }

        return $this->result = $res;
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

