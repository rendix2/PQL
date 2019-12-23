<?php
namespace query;

use Alias;
use Condition;
use Exception;
use FunctionPql;
use JoinedTable;
use Netpromotion\Profiler\Profiler;
use Optimizer;
use Query;
use query\Join\NestedLoopJoin;
use query\Join\HashJoin;
use query\Join\SortMergeJoin;
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
     * @var int $countGroupedByData
     */
    private $countGroupedByData;

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
        $this->countGroupedByData = null;

        parent::__destruct();
    }

    /**
     * @return array|Row[]
     */
    public function run()
    {
        $this->checkColumns();

        Profiler::start('getRows');
        $this->result = $this->fromTableAliases();
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

        bdump($this->result, '$this->result GROUP');

        Profiler::start('functions');
        $this->functions();
        Profiler::finish('functions');

        bdump($this->result, '$this->result FUNCTIONS');

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

        Profiler::start('UNION');
        $this->union();
        Profiler::finish('UNION');

        //bdump($this->result, '$this->result UNION');

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
        
        foreach ($this->query->getInnerJoinedTables() as $innerJoinedTable) {
            foreach ($innerJoinedTable->getTable()->getColumns() as $column) {
                $columns[] = $column->getName();

                if ($innerJoinedTable->hasAlias()) {
                    $columns[] = $innerJoinedTable->getAlias()->getTo() . Alias::DELIMITER . $column->getName();
                }
            }
        }

        foreach ($this->query->getLeftJoinedTables() as $leftJoinedTable) {
            foreach ($leftJoinedTable->getTable()->getColumns() as $column) {
                $columns[] = $column->getName();

                if ($leftJoinedTable->hasAlias()) {
                    $columns[] = $leftJoinedTable->getAlias()->getTo() . Alias::DELIMITER . $column->getName();
                }
            }
        }

        foreach ($this->query->getRightJoinedTables() as $rightJoinedTable) {
            foreach ($rightJoinedTable->getTable()->getColumns() as $column) {
                $columns[] = $column->getName();

                if ($rightJoinedTable->hasAlias()) {
                    $columns[] = $rightJoinedTable->getAlias()->getTo() . Alias::DELIMITER . $column->getName();
                }
            }
        }

        foreach ($this->query->getCrossJoinedTables() as $crossJoinedTable) {
            foreach ($crossJoinedTable->getTable()->getColumns() as $column) {
                $columns[] = $column->getName();

                if ($crossJoinedTable->hasAlias()) {
                    $columns[] = $crossJoinedTable->getAlias()->getTo() . Alias::DELIMITER . $column->getName();
                }
            }
        }

        if ($this->query->getTable() instanceof Table) {
            foreach ($this->query->getTable()->getColumns() as $column) {
                $columns[] = $column->getName();

                if ($this->query->getTableAlias()) {
                    $columns[] = $this->query->getTableAlias()->getTo() . Alias::DELIMITER . $column->getName();
                }
            }
        } elseif ($this->query->getTable() instanceof Query) {
            $columns = array_merge($columns, $this->query->getTable()->getSelectedColumns());
        }

        foreach ($this->query->getSelectedColumns() as $column) {
            if (!in_array($column, $columns, true)) {
                throw new Exception(sprintf('Selected column "%s" does not exists.', $column));
            }
        }
    }

    /**
     * @param string $functionColumnName
     * @param mixed  $functionResult
     */
    private function addFunctionIntoResult($functionColumnName, $functionResult)
    {
        if ($this->query->getSelectedColumns()) {
            $this->query->selectedColumns[] = $functionColumnName;

            foreach ($this->result as &$row) {
                $row[$functionColumnName] = $functionResult;
            }

            unset($row);
        } else {
            $this->query->selectedColumns[] = $functionColumnName;
            $this->result           = [0 => [$functionColumnName => $functionResult]];
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
                if ($this->countGroupedByData) {
                    bdump('has grouped');
                    $this->query->selectedColumns[] = $function_column_name;
                    $functionGroupByResult  = [];

                    // iterate over grouped data!
                    foreach ($this->groupedByData as $groupedByColumn => $groupedValues) {
                        foreach ($groupedValues as $groupedValue => $groupedRows) {
                            bdump($groupedRows['rows'], '$groupedRows[\'rows\']');

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
                    bdump('has  not grouped');

                    $this->addFunctionIntoResult($function_column_name, $functions->sum($column));
                }
            }

            if ($function_name === FunctionPql::COUNT) {
                if ($this->countGroupedByData) {
                    $this->query->selectedColumns[] = $function_column_name;

                    foreach ($this->result as &$row) {
                        $row[$function_column_name] = $row['__group_count'];
                    }

                    unset($row);
                } else {
                    $this->addFunctionIntoResult($function_column_name, $functions->count($column));
                }
            }

            if ($function_name === FunctionPql::AVERAGE) {
                if ($this->countGroupedByData) {
                    $this->query->selectedColumns[] = $function_column_name;
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
                if ($this->countGroupedByData) {
                    $this->query->selectedColumns[] = $function_column_name;
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
                if ($this->countGroupedByData) {
                    $this->query->selectedColumns[] = $function_column_name;
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
                if ($this->countGroupedByData) {
                    $this->query->selectedColumns[] = $function_column_name;
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
        if (!$this->query->hasInnerJoinedTable()) {
            return $this->result;
        }

        foreach ($this->query->getInnerJoinedTables() as $innerJoinedTable) {
            foreach ($innerJoinedTable->getOnConditions() as $condition) {
                $innerJoinedTableRows = $this->joinedTableAliases($innerJoinedTable);

                switch ($this->optimizer->sayJoinAlgorithm($condition)) {
                    case Optimizer::MERGE_JOIN:
                        $this->result = SortMergeJoin::innerJoin($this->result, $innerJoinedTableRows, $condition);
                        break;

                    case Optimizer::HASH_JOIN:
                        $this->result = HashJoin::innerJoin($this->result, $innerJoinedTableRows, $condition);
                        break;

                    case Optimizer::NESTED_LOOP:
                        $this->result = NestedLoopJoin::innerJoin($this->result, $innerJoinedTableRows, $condition);
                        break;
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
        if (!$this->query->hasCrossJoinedTable()) {
            return $this->result;
        }

        foreach ($this->query->getCrossJoinedTables() as $crossJoinedTable) {
            $crossJoinedTableRows = $this->joinedTableAliases($crossJoinedTable);

            $this->result = NestedLoopJoin::crossJoin($this->result, $crossJoinedTableRows);
        }

        return $this->result;
    }

    /**
     * @return array|Row[]
     * @throws Exception
     */
    private function leftJoin()
    {
        if (!$this->query->hasLeftJoinedTable()) {
            return $this->result;
        }

        foreach ($this->query->getLeftJoinedTables() as $leftJoinedTable) {
            /**
             * @var Condition $condition
             */
            foreach ($leftJoinedTable->getOnConditions() as $condition) {
                $leftJoinedTableRows = $this->joinedTableAliases($leftJoinedTable);

                switch ($this->optimizer->sayJoinAlgorithm($condition)) {
                    case Optimizer::MERGE_JOIN:
                        $this->result = SortMergeJoin::leftJoin($this->result, $leftJoinedTableRows, $condition);
                        break;

                    case Optimizer::HASH_JOIN:
                        $this->result = HashJoin::leftJoin($this->result, $leftJoinedTableRows, $condition);
                        break;

                    case Optimizer::NESTED_LOOP:
                        $this->result = NestedLoopJoin::leftJoin($this->result, $leftJoinedTableRows, $condition);
                        break;
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
        if (!$this->query->hasRightJoinedTable()) {
            return $this->result;
        }

        foreach ($this->query->getRightJoinedTables() as $rightJoinedTable) {
            foreach ($rightJoinedTable->getOnConditions() as $condition) {
                $rightJoinedTableRows = $this->joinedTableAliases($rightJoinedTable);

                switch ($this->optimizer->sayJoinAlgorithm($condition)) {
                    case Optimizer::MERGE_JOIN:
                        $this->result = SortMergeJoin::rightJoin($this->result, $rightJoinedTableRows, $condition);
                        break;

                    case Optimizer::HASH_JOIN:
                        $this->result = HashJoin::rightJoin($this->result, $rightJoinedTableRows, $condition);
                        break;

                    case Optimizer::NESTED_LOOP:
                        $this->result = NestedLoopJoin::rightJoin($this->result, $rightJoinedTableRows, $condition);
                        break;
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
        if (!$this->query->hasFullJoinedTable()) {
            return $this->result;
        }

        foreach ($this->query->getFullJoinedTables() as $fullJoinedTable) {
            foreach ($fullJoinedTable->getOnConditions() as $condition) {
                $fullJoinedTableRows = $this->joinedTableAliases($fullJoinedTable);

                switch ($this->optimizer->sayJoinAlgorithm($condition)) {
                    case Optimizer::MERGE_JOIN:
                        $this->result = SortMergeJoin::fullJoin($this->result, $fullJoinedTableRows, $condition);
                        break;

                    case Optimizer::HASH_JOIN:
                        $this->result = HashJoin::fullJoin($this->result, $fullJoinedTableRows, $condition);
                        break;

                    case Optimizer::NESTED_LOOP:
                        $this->result = NestedLoopJoin::fullJoin($this->result, $fullJoinedTableRows, $condition);
                        break;
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
        if (!$this->query->hasWhereCondition()) {
            return $this->result;
        }

        foreach ($this->query->getWhereConditions() as $whereCondition) {
            $this->result = $this->doWhere($this->result, $whereCondition);
        }

        return $this->result;
    }

    /**
     * @return array|Row[]
     */
    private function groupBy()
    {        
        if (!$this->query->hasGroupBy()) {
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

        $this->groupedByData      = $groups;

        bdump($this->groupedByData, '$this->groupedByData');

        $this->countGroupedByData = count($groups);

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
        if (!$this->query->hasHavingCondition()) {
            return $this->result;
        }

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
        if (!$this->optimizer->sayIfOrderByIsNeed() || !$this->query->hasOrderBy()) {
            return $this->result;
        }

        $tmpSort = [];
        $tmp     = [];
            
        foreach ($this->result as $column => $values) {
            foreach ($values as $key => $value) {
                $tmp[$column][$key] = $value;
            }
        }

        foreach ($this->query->getOrderBy() as $orderBy) {
            $tmpSort[] = array_column($tmp, $orderBy->getColumn());
            $tmpSort[] = $orderBy->getSortingConst();
            $tmpSort[] = SORT_REGULAR;
        }
            
        $tmpSort[] = &$tmp;
        $sortRes   = array_multisort(...$tmpSort);

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
                if (in_array($column, $this->query->getSelectedColumns(), true)) {
                    $newRow->get()->{$column} = $value;
                }
            }
            $columnObj[] = $newRow;
        }
        
        return $columnObj;
    }

    /**
     * @param JoinedTable $table
     *
     * @return array
     */
    private function joinedTableAliases(JoinedTable $table)
    {
        if (!$table->hasAlias()) {
            return $this->result;
        }

        $result = [];
        $rows = $table->getTable()->getRows();

        foreach ($rows as $rowNumber => $row) {
            foreach ($row as $columnName => $columnValue) {
                $result[$rowNumber][$table->getAlias()->getTo() . Alias::DELIMITER . $columnName] = $columnValue;
                $result[$rowNumber][$columnName] = $columnValue;
            }
        }

        return $result;
    }

    /**
     * @return array|Row[]
     */
    private function fromTableAliases()
    {
        if ($this->query->hasTableAlias()) {
            $rows = $this->query->getTable()->getRows();
            $result = [];

            foreach ($rows as $rowNumber => $row) {
                foreach ($row as $columnName => $columnValue) {
                    $result[$rowNumber][$this->query->getTableAlias()->getTo() . Alias::DELIMITER . $columnName] = $columnValue;
                    $result[$rowNumber][$columnName] = $columnValue;
                }
            }

            return $result;
        } else {
            if ($this->query->getTable() instanceof Table) {
                return $this->query->getTable()->getRows();
            } elseif ($this->query->getTable() instanceof Query) {
                return $this->query->getTable()->run()->getQuery()->getResult();
            }
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    private function union()
    {
        foreach ($this->query->getUnion() as $unionQuery) {
            if ($unionQuery->getType() !== Query::SELECT)  {
                throw new Exception('Unioned query is not select query.');
            }

            $runResult = $unionQuery->run();

            if (count($runResult->getColumns()) !== count($this->query->getSelectedColumns())) {
                throw new Exception('Unioned query has not the same count of columns as a main query.');
            }

            $this->result = array_merge($this->result, $runResult->getQuery()->getResult());
        }

        return $this->result;
    }
}

