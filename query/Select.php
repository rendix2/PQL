<?php
namespace query;

use Column;
use Condition;
use Exception;
use FunctionPql;
use Netpromotion\Profiler\Profiler;
use Operator;
use Optimizer;
use OrderBy;
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
     * @var array $groupedByData
     */
    private $groupedByData;

    /**
     * @var Optimizer $optimizer
     */
    private $optimizer;

    /**
     * Select constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        parent::__construct($query);

        $this->optimizer = new Optimizer($query);
    }

    /**
     * Select destructor.
     */
    public function __destruct()
    {
        $this->groupedByData = null;
        $this->optimizer = null;

        parent::__destruct();
    }

    /**
     * @return array|Row[]
     */
    public function run()
    {
        $this->checkColumns();

        Profiler::start('getRows');
        $this->result = $this->query->getTable()->getRows();
        Profiler::finish('getRows');

        //bdump($this->result, '$this->result SET');

        Profiler::start('innerJoin');
        $this->innerJoin();
        Profiler::finish('innerJoin');

        //bdump($this->result, '$this->result INNER');

        Profiler::start('crossJoin');
        $this->crossJoin();
        Profiler::finish('crossJoin');

        //bdump($this->result, '$this->result CROSS');

        Profiler::start('leftJoin');
        $this->leftJoin();
        Profiler::finish('leftJoin');

        //bdump($this->result, '$this->result LEFT');

        Profiler::start('rightJoin');
        $this->rightJoin();
        Profiler::finish('rightJoin');

        //bdump($this->result, '$this->result RIGHT');

        Profiler::start('fullJoin');
        $this->fullJoin();
        Profiler::finish('fullJoin');

        //bdump($this->result, '$this->result FULL');

        Profiler::start('where');
        $this->where();
        Profiler::finish('where');

        //bdump($this->result, '$this->result WHERE');

        Profiler::start('groupBy');
        $this->groupBy();
        Profiler::finish('groupBy');

        //bdump($this->result, '$this->result GROUP');

        Profiler::start('functions');
        $this->functions();
        Profiler::finish('functions');

        //bdump($this->result, '$this->result FUNCTIONS');

        Profiler::start('having');
        $this->having();
        Profiler::finish('having');

        //bdump($this->result, '$this->result HAVING');

        Profiler::start('orderBy');
        $this->orderBy();
        Profiler::finish('orderBy');

        //bdump($this->result, '$this->result ORDER');

        Profiler::start('limit');
        $this->limit();
        Profiler::finish('limit');

       // bdump($this->result, '$this->result LIMIT');

        Profiler::start('createRows');
        $rows =  $this->createRows();
        Profiler::finish('createRows');

        return $rows;
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
     * @param string $function_column_name
     * @param mixed  $functionResult
     */
    private function addFunctionIntoResult($function_column_name, $functionResult)
    {
        if ($this->query->getColumns()) {
            $this->query->columns[] = $function_column_name;

            foreach ($this->result as &$row) {
                $row[$function_column_name] = $functionResult;
            }

            unset($row);
        } else {
            $this->query->columns[] = $function_column_name;
            $this->result           = [0 => [$function_column_name => $functionResult]];
        }
    }

    /**
     * add desired data into result
     *
     * @param string $column
     * @param array  $groupedByResult
     * @param string $function_column_name
     */
    private function addGroupedFunctionDataIntoResult($column, array $groupedByResult, $function_column_name)
    {
        foreach ($this->result as &$row) {
            foreach ($groupedByResult as $groupedByColumn => $groupedByValues) {
                $row[$function_column_name] = $groupedByValues[$row[$groupedByColumn]][$column];
            }
        }

        unset($row);
    }

    /**
     *
     */
    private function functions()
    {
        $functions = new Functions($this->result);

        /**
         * @var FunctionPql $function
         */
        foreach ($this->query->getFunctions() as $function) {
            $function_name = $function->getName();
            $column        = $function->getParams()[0];

            $function_column_name = sprintf('%s(%s)', mb_strtoupper($function_name), $column);

            if ($function_name === FunctionPql::SUM) {
                if (count($this->groupedByData)) {
                    $this->query->columns[] = $function_column_name;
                    $functionGroupByResult  = [];

                    // iterate over grouped data!
                    foreach ($this->groupedByData as $groupedByColumn => $groupedValues) {
                        foreach ($groupedValues as $groupedValue => $groupedRows) {
                            foreach ($groupedRows['rows'] as $row) {
                                if (isset($functionGroupByResult[$groupedByColumn][$groupedValue][$column])) {
                                    $functionGroupByResult[$groupedByColumn][$groupedValue][$column] += $row[$column];
                                } else {
                                    $functionGroupByResult[$groupedByColumn][$groupedValue][$column] = $row[$column];
                                }
                            }
                        }
                    }

                    $this->addGroupedFunctionDataIntoResult($column, $functionGroupByResult, $function_column_name);
                } else {
                    $this->addFunctionIntoResult($function_column_name, $functions->sum($column));
                }
            }

            if ($function_name === FunctionPql::COUNT) {
                if (count($this->groupedByData)) {
                    $this->query->columns[] = $function_column_name;

                    foreach ($this->result as &$row) {
                        $row[$function_column_name] = $row['__group_count'];
                    }

                    unset($row);
                } else {
                    $this->addFunctionIntoResult($function_column_name, $functions->count($column));
                }
            }

            if ($function_name === FunctionPql::AVERAGE) {
                if (count($this->groupedByData)) {
                    $this->query->columns[] = $function_column_name;
                    $functionGroupByResult  = [];

                    foreach ($this->groupedByData as $groupedByColumn => $groupedByValues) {
                        foreach ($groupedByValues as $groupedByValue => $groupedRows) {
                            foreach ($groupedRows['rows'] as $groupedRow) {
                                if (isset($functionGroupByResult[$groupedByColumn][$groupedByValue][$column])) {
                                    $functionGroupByResult[$groupedByColumn][$groupedByValue][$column] += $groupedRow[$column];
                                } else {
                                    $functionGroupByResult[$groupedByColumn][$groupedByValue][$column] = $groupedRow[$column];
                                }
                            }

                            $functionGroupByResult[$groupedByColumn][$groupedByValue][$column] /= $groupedRows['__group_count'];
                        }
                    }

                    $this->addGroupedFunctionDataIntoResult($column, $functionGroupByResult, $function_column_name);
                } else {
                    $this->addFunctionIntoResult($function_column_name, $functions->avg($column));
                }
            }

            if ($function_name === FunctionPql::MIN) {
                if (count($this->groupedByData)) {
                    $this->query->columns[] = $function_column_name;
                    $functionGroupByResult  = [];

                    foreach ($this->groupedByData as $groupedByColumn => $groupedByValues) {
                        foreach ($groupedByValues as $groupedByValue => $groupedRows) {
                            foreach ($groupedRows['rows'] as $groupedRow) {
                                if (!isset($functionGroupByResult[$groupedByColumn][$groupedByValue][$column])) {
                                    $functionGroupByResult[$groupedByColumn][$groupedByValue][$column] = INF;
                                }

                                if ($groupedRow[$column] < $functionGroupByResult[$groupedByColumn][$groupedByValue][$column] ) {
                                    $functionGroupByResult[$groupedByColumn][$groupedByValue][$column] = $groupedRow[$column];
                                }
                            }
                        }
                    }

                    $this->addGroupedFunctionDataIntoResult($column, $functionGroupByResult, $function_column_name);
                } else {
                    $this->addFunctionIntoResult($function_column_name, $functions->min($column));
                }
            }

            if ($function_name === FunctionPql::MAX) {
                if (count($this->groupedByData)) {
                    $this->query->columns[] = $function_column_name;
                    $functionGroupByResult  = [];

                    foreach ($this->groupedByData as $groupedByColumn => $groupedByValues) {
                        foreach ($groupedByValues as $groupedByValue => $groupedRows) {
                            foreach ($groupedRows['rows'] as $groupedRow) {
                                if (!isset($functionGroupByResult[$groupedByColumn][$groupedByValue][$column])) {
                                    $functionGroupByResult[$groupedByColumn][$groupedByValue][$column] = -INF;
                                }

                                if ($groupedRow[$column] > $functionGroupByResult[$groupedByColumn][$groupedByValue][$column] ) {
                                    $functionGroupByResult[$groupedByColumn][$groupedByValue][$column] = $groupedRow[$column];
                                }
                            }
                        }
                    }

                    $this->addGroupedFunctionDataIntoResult($column, $functionGroupByResult, $function_column_name);
                } else {
                    $this->addFunctionIntoResult($function_column_name, $functions->max($column));
                }
            }

            if ($function_name === FunctionPql::MEDIAN) {
                if (count($this->groupedByData)) {
                    $this->query->columns[] = $function_column_name;
                    $functionGroupByResult  = [];

                    $tmp = [];

                    foreach ($this->groupedByData as $groupedByColumn => $groupedByValues) {
                        foreach ($groupedByValues as $groupedByValue => $groupedRows) {
                            foreach ($groupedRows['rows'] as $groupedRow) {
                                $tmp[$groupedByColumn][$groupedByValue][$column][] = $groupedRow[$column];
                            }

                            sort($tmp[$groupedByColumn][$groupedByValue][$column]);

                            $count = count($tmp[$groupedByColumn][$groupedByValue][$column]);

                            if ($count % 2) {
                                $functionGroupByResult[$groupedByColumn][$groupedByValue][$column] = $tmp[$groupedByColumn][$groupedByValue][$column][$count / 2];
                            } else {
                                $functionGroupByResult[$groupedByColumn][$groupedByValue][$column] =
                                    (
                                        $tmp[$groupedByColumn][$groupedByValue][$column][$count / 2 ] +
                                        $tmp[$groupedByColumn][$groupedByValue][$column][$count / 2 - 1]
                                    ) / 2;
                            }
                        }
                    }

                    $this->addGroupedFunctionDataIntoResult($column, $functionGroupByResult, $function_column_name);
                } else {
                    $this->addFunctionIntoResult($function_column_name, $functions->median($column));
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
                if ($condition->getOperator() === Operator::EQUAL) {
                    $this->result = HashJoin::leftJoin($this->result, $leftJoinedTable['table']->getRows(), $condition);
                } else {
                    $this->result = NestedLoopJoin::leftJoin($this->result, $leftJoinedTable['table']->getRows(), $condition);
                }
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
                if ($condition->getOperator() === Operator::EQUAL) {
                    $this->result = HashJoin::rightJoin($this->result, $rightJoinedTable['table']->getRows(), $condition);
                } else {
                    $this->result = NestedLoopJoin::rightJoin($this->result, $rightJoinedTable['table']->getRows(), $condition);
                }
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
                if ($condition->getOperator() === Operator::EQUAL) {
                    $this->result = HashJoin::fullJoin($this->result, $fullJoinedTable['table']->getRows(), $condition);
                } else {
                    $this->result = NestedLoopJoin::fullJoin($this->result, $fullJoinedTable['table']->getRows(), $condition);
                }
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

        $this->groupedByData = $groups;

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

        /**
         * @var OrderBy $orderBy
         */
        foreach ($this->query->getOrderBy() as $orderBy) {
            $tmpSort[] = array_column($tmp, $orderBy->getColumn());
            $tmpSort[] = $orderBy->getSortingConst();
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

