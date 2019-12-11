<?php
namespace query;

use Column;
use Condition;
use Exception;
use Operator;
use Query;
use query\Join\NestedLoopJoin;
use query\Join\HashJoin;
use Row;
use Table;

/**
 * Class Select
 *
 * @package query
 */
class Select extends BaseQuery
{
    /**
     * @return array|Row[]
     */
    public function run()
    {
        $this->checkColumns();

        $this->result = $this->query->getTable()->getRows();

        //bdump($this->result, '$this->result SET');
        
        $this->innerJoin();

        //bdump($this->result, '$this->result INNER');

        $this->crossJoin();

        //bdump($this->result, '$this->result CROSS');

        $this->leftJoin();

        //bdump($this->result, '$this->result LEFT');

        $this->rightJoin();

        //bdump($this->result, '$this->result RIGHT');

        $this->fullJoin();

        //bdump($this->result, '$this->result FULL');

        $this->where();

        //bdump($this->result, '$this->result WHERE');

        $this->groupBy();

        //bdump($this->result, '$this->result GROUP');

        $this->functions();

        //bdump($this->result, '$this->result FUNCTIONS');

        $this->having();

        //bdump($this->result, '$this->result HAVING');

        $this->orderBy();

        //bdump($this->result, '$this->result ORDER');

        $this->limit();

       // bdump($this->result, '$this->result LIMIT');

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
            foreach ($table['table']->getColumns() as $column) {
                $columns[] = $column->getName();
            }
        }
        
        foreach ($this->query->getLeftJoin() as $table) {
            foreach ($table['table']->getColumns() as $column) {
                $columns[] = $column->getName();
            }
        }

        foreach ($this->query->getRightJoin() as $table) {
            foreach ($table['table']->getColumns() as $column) {
                $columns[] = $column->getName();
            }
        }

        foreach ($this->query->getCrossJoin() as $table) {
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

                    $keysToGroup = array_keys($this->query->getGrouped());
                    $tmpFunc = [];

                    // iterate over grouped data!
                    foreach ($keysToGroup as $groupByColumn) {
                        foreach ($this->query->getGrouped()[$groupByColumn] as $groupedValue => $groupedData) {
                            foreach ($groupedData['rows'] as $row) {
                                if (isset($tmpFunc[$groupByColumn][$groupedValue][$column])) {
                                    $tmpFunc[$groupByColumn][$groupedValue][$column] += $row[$column];
                                } else {
                                    $tmpFunc[$groupByColumn][$groupedValue][$column] = $row[$column];
                                }
                            }
                        }
                    }

                    // add desired data into result
                    foreach ($this->result as &$row) {
                        foreach ($tmpFunc as $groupedByColumn => $groupedByValues) {
                            $row[$function_column_name] = $groupedByValues[$row[$groupedByColumn]][$column];
                        }
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

        foreach ($this->query->getInnerJoin() as $innerJoinedTable) {
            /**
             * @var Condition $condition
             */
            foreach ($innerJoinedTable['onConditions'] as $condition) {
                // equi join
                if ($condition->getOperator() === Operator::EQUAL) {
                    $this->result = HashJoin::innerJoin($this->result, $innerJoinedTable['table']->getRows(), $condition);
                } else {
                    $this->result = NestedLoopJoin::innerJoin($this->result, $innerJoinedTable['table']->getRows(), $condition);
                }
            }
        }

        return $this->result;
    }

    /**
     * @return array
     */
    private function crossJoin()
    {
        if (!count($this->query->getCrossJoin())) {
            return $this->result;
        }

        foreach ($this->query->getCrossJoin() as $crossJoinedTable) {
            $this->result = NestedLoopJoin::crossJoin($this->result, $crossJoinedTable->getRows());
        }

        return $this->result;
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

        foreach ($this->query->getleftJoin() as $leftJoinedTable) {
            /**
             * @var Condition $condition
             */
            foreach ($leftJoinedTable['onConditions'] as $condition) {
                $this->result = HashJoin::leftJoin($this->result, $leftJoinedTable['table']->getRows(), $condition);
            }
        }

        return $this->result;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function rightJoin()
    {
        if (!count($this->query->getRightJoin())) {
            return $this->result;
        }

        foreach ($this->query->getRightJoin() as $rightJoinedTable) {
            if (!count($rightJoinedTable['onConditions'])) {
                throw new Exception('No ON condition.');
            }

            /**
             * @var Condition $condition
             */
            foreach ($rightJoinedTable['onConditions'] as $condition) {
                $this->result = HashJoin::rightJoin($this->result, $rightJoinedTable['table']->getRows(), $condition);
            }
        }

        return $this->result;
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    private function fullJoin()
    {
        if (!count($this->query->getFullJoin())) {
            return $this->result;
        }

        foreach ($this->query->getFullJoin() as $fullJoinedTable) {
            if (!count($fullJoinedTable['onConditions'])) {
                throw new Exception('No ON condition.');
            }

            /**
             * @var Condition $condition
             */
            foreach ($fullJoinedTable['onConditions'] as $condition) {
                $this->result = NestedLoopJoin::fullJoin($this->result, $fullJoinedTable['table']->getRows(), $condition);
            }
        }

        return $this->result;
    }

    /**
     * @param array     $rows
     * @param Condition $condition
     *
     * @return array
     *
     */
    private function doWhere(array $rows, Condition $condition)
    {
        $whereResult = [];

        foreach ($rows as $row) {
            if (ConditionHelper::condition($condition, $row, [])) {
                $whereResult[] = $row;
            }
        }

        return $whereResult;
    }

    /**
     * @return array|Row[]
     * @throws Exception
     */
    private function where()
    {
        if (!count($this->query->getWhereCondition())) {
            return $this->result;
        }

        foreach ($this->query->getWhereCondition() as $condition) {
            $this->result = $this->doWhere($this->result, $condition);
        }

        return $this->result;
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
                        $groups[$column][$value]['rows'][] = $row;

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
     * @param array     $rows
     * @param Condition $condition
     *
     * @return array
     */
    private function doHaving(array $rows, Condition $condition)
    {
        $havingResult = [];

        foreach ($rows as $row) {
            if (ConditionHelper::condition($condition, $row, [])) {
                $havingResult[] = $row;
            }
        }

        return $havingResult;
    }

    /**
     * @return array|Row[]
     */
    private function having()
    {
        if (!count($this->query->getHavingConditions())) {
            return $this->result;
        }

        /**
         * @var Condition $havingCondition
         */
        foreach ($this->query->getHavingConditions() as $havingCondition) {
            $this->result = $this->doHaving($this->result, $havingCondition);
        }

        return $this->result;
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

