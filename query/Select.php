<?php
namespace query;

use Alias;
use Condition;
use Exception;
use AggregateFunctions;
use JoinedTable;
use Netpromotion\Profiler\Profiler;
use Optimizer;
use Query;
use query\Join\NestedLoopJoin;
use query\Join\HashJoin;
use query\Join\SortMergeJoin;
use Row;
use SelectedColumn;
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
     * @var int $groupedByDataCount
     */
    private $groupedByDataCount;

    /**
     * @var Optimizer $optimizer
     */
    private $optimizer;

    /**
     * @var SelectedColumn $columns
     */
    private $columns;

    /**
     * Select constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        parent::__construct($query);

        $this->optimizer = new Optimizer($query);
        $this->columns = [];
    }

    /**
     * Select destructor.
     */
    public function __destruct()
    {
        $this->groupedByData      = null;
        $this->optimizer          = null;
        $this->groupedByDataCount = null;
        $this->columns            = null;

        parent::__destruct();
    }

    /**
     * @return SelectedColumn[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return Row[]
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

        Profiler::start('UNION');
        $this->union();
        Profiler::finish('UNION');

        //bdump($this->result, '$this->result UNION');

        Profiler::start('UNION ALL');
        $this->unionAll();
        Profiler::finish('UNION ALL');

        //bdump($this->result, '$this->result UNION ALL');

        Profiler::start('INTERSECT');
        $this->intersect();
        Profiler::finish('INTERSECT');

        //bdump($this->result, '$this->result INTERSECT');

        Profiler::start('EXCEPT');
        $this->except();
        Profiler::finish('EXCEPT');

        //bdump($this->result, '$this->result EXCEPT');

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
            $columns = array_merge(
                $columns,
                $this->query->getTable()->getSelectedColumns(),
                $this->query->getTable()->getResult()->getQuery()->getColumns()
            );
        }

        foreach ($this->query->getSelectedColumns() as $column) {
            if (!in_array($column->getColumn(), $columns, true)) {
                throw new Exception(sprintf('Selected column "%s" does not exists.', $column->getColumn()));
            }
        }
    }

    /**
     * @param string $functionColumnName
     * @param mixed  $functionResult
     */
    private function addFunctionIntoResult($functionColumnName, $functionResult)
    {
        $this->columns[] = new SelectedColumn($functionColumnName);

        if ($this->query->getSelectedColumns()) {
            foreach ($this->result as &$row) {
                $row[$functionColumnName] = $functionResult;
            }

            unset($row);
        } else {
            $this->result = [0 => [$functionColumnName => $functionResult]];
        }
    }

    /**
     * add desired data into result
     *
     * @param string $column
     * @param array  $groupedByResult
     * @param string $functionColumnName
     */
    private function addGroupedFunctionDataIntoResult($column, array $groupedByResult, $functionColumnName)
    {
        foreach ($this->result as &$row) {
            $row[$functionColumnName] = $groupedByResult[$column][$row[$column]];
        }

        unset($row);
    }

    /**
     *
     */
    private function functions()
    {
        $functions = new Functions($this->result);

        foreach ($this->query->getFunctions() as $function) {
            $functionName = $function->getName();
            $column       = $function->getParams()[0];

            $functionColumnName = sprintf('%s(%s)', mb_strtoupper($functionName), $column);

            if ($functionName === AggregateFunctions::SUM) {
                if ($this->groupedByDataCount) {
                    $this->columns[] = new SelectedColumn($functionColumnName);
                    $functionGroupByResult  = [];

                    // iterate over grouped data!
                    foreach ($this->groupedByData as $groupByColumn => $groupByRows) {
                        foreach ($groupByRows as $groupByValue => $groupedRows) {
                            foreach ($groupedRows as $groupedRow) {
                                if (isset($functionGroupByResult[$groupByColumn][$groupByValue])) {
                                    $functionGroupByResult[$groupByColumn][$groupByValue] += $groupedRow[$column];
                                } else {
                                    $functionGroupByResult[$groupByColumn][$groupByValue] = $groupedRow[$column];
                                }
                            }
                        }

                        $this->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
                    }
                } else {
                    $this->addFunctionIntoResult($functionColumnName, $functions->sum($column));
                }
            }

            if ($functionName === AggregateFunctions::COUNT) {
                if ($this->groupedByDataCount) {
                    $this->columns[] = new SelectedColumn($functionColumnName);
                    $functionGroupByResult = [];

                    foreach ($this->groupedByData as $groupedByColumn => $groupByRows) {
                        foreach ($groupByRows as $groupByValue => $groupedRows) {
                            $functionGroupByResult[$groupedByColumn][$groupByValue] = count($groupedRows);
                        }

                        $this->addGroupedFunctionDataIntoResult($groupedByColumn, $functionGroupByResult, $functionColumnName);
                    }
                } else {
                    $this->addFunctionIntoResult($functionColumnName, $functions->count($column));
                }
            }

            if ($functionName === AggregateFunctions::AVERAGE) {
                if ($this->groupedByDataCount) {
                    $this->columns[] = new SelectedColumn($functionColumnName);
                    $functionGroupByResult  = [];

                    foreach ($this->groupedByData as $groupByColumn => $groupByRows) {
                        foreach ($groupByRows as $groupByValue => $groupedRows) {
                            foreach ($groupedRows as $groupedRow) {
                                if (isset($functionGroupByResult[$groupByColumn][$groupByValue])) {
                                    $functionGroupByResult[$groupByColumn][$groupByValue] += $groupedRow[$column];
                                } else {
                                    $functionGroupByResult[$groupByColumn][$groupByValue] = $groupedRow[$column];
                                }
                            }

                            $functionGroupByResult[$groupByColumn][$groupByValue] /= count($groupedRows);
                        }

                        $this->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
                    }
                } else {
                    $this->addFunctionIntoResult($functionColumnName, $functions->avg($column));
                }
            }

            if ($functionName === AggregateFunctions::MIN) {
                if ($this->groupedByDataCount) {
                    $this->columns[] = new SelectedColumn($functionColumnName);
                    $functionGroupByResult  = [];

                    foreach ($this->groupedByData as $groupByColumn => $groupByRows) {
                        foreach ($groupByRows as $groupByValue => $groupedRows) {
                            foreach ($groupedRows as $groupedRow) {
                                if (!isset($functionGroupByResult[$groupByColumn][$groupByValue])) {
                                    $functionGroupByResult[$groupByColumn][$groupByValue] = INF;
                                }

                                if ($groupedRow[$column] < $functionGroupByResult[$groupByColumn][$groupByValue] ) {
                                    $functionGroupByResult[$groupByColumn][$groupByValue]= $groupedRow[$column];
                                }
                            }
                        }

                        $this->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
                    }
                } else {
                    $this->addFunctionIntoResult($functionColumnName, $functions->min($column));
                }
            }

            if ($functionName === AggregateFunctions::MAX) {
                if ($this->groupedByDataCount) {
                    $this->columns[] = new SelectedColumn($functionColumnName);
                    $functionGroupByResult  = [];

                    foreach ($this->groupedByData as $groupByColumn => $groupByRows) {
                        foreach ($groupByRows as $groupByValue => $groupedRows) {
                            foreach ($groupedRows as $groupedRow) {
                                if (!isset($functionGroupByResult[$groupByColumn][$groupByValue])) {
                                    $functionGroupByResult[$groupByColumn][$groupByValue] = -INF;
                                }

                                if ($groupedRow[$column] > $functionGroupByResult[$groupByColumn][$groupByValue] ) {
                                    $functionGroupByResult[$groupByColumn][$groupByValue] = $groupedRow[$column];
                                }
                            }
                        }

                        $this->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
                    }
                } else {
                    $this->addFunctionIntoResult($functionColumnName, $functions->max($column));
                }
            }

            if ($functionName === AggregateFunctions::MEDIAN) {
                if ($this->groupedByDataCount) {
                    $this->columns[] = new SelectedColumn($functionColumnName);
                    $functionGroupByResult  = [];

                    $tmp = [];

                    foreach ($this->groupedByData as $groupByColumn => $groupByRows) {
                        foreach ($groupByRows as $groupByValue => $groupedRows) {
                            foreach ($groupedRows as $groupedRow) {
                                $tmp[$groupByColumn][$groupByValue][] = $groupedRow[$column];
                            }

                            sort($tmp[$groupByColumn][$groupByValue]);

                            $count = count($tmp[$groupByColumn][$groupByValue]);

                            if ($count % 2) {
                                $functionGroupByResult[$groupByColumn][$groupByValue] = $tmp[$groupByColumn][$groupByValue][$count / 2];
                            } else {
                                $functionGroupByResult[$groupByColumn][$groupByValue]=
                                    (
                                        $tmp[$groupByColumn][$groupByValue][$count / 2 ] +
                                        $tmp[$groupByColumn][$groupByValue][$count / 2 - 1]
                                    ) / 2;
                            }
                        }

                        $this->addGroupedFunctionDataIntoResult($groupByColumn, $functionGroupByResult, $functionColumnName);
                    }
                } else {
                    $this->addFunctionIntoResult($functionColumnName, $functions->median($column));
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
     * @param array  $rows
     * @param string $column
     *
     * @return array
     */
    private function doGroupBy(array $rows, $column)
    {
        $groupByTemp = [];

        foreach ($rows as $row) {
            $groupByTemp[$column][$row[$column]][] = $row;
        }

        $this->groupedByData      = $groupByTemp;
        $this->groupedByDataCount = count($this->groupedByData);

        $result = [];

        foreach ($groupByTemp[$column] as $groupRows) {
            $result[] = $groupRows[0];
        }

        return $result;
    }

    /**
     * @return array|Row[]
     */
    private function groupBy()
    {        
        if (!$this->query->hasGroupBy()) {
            return  $this->result;
        }
        
        foreach ($this->query->getGroupByColumns() as $groupByColumn) {
            $this->result = $this->doGroupBy($this->result, $groupByColumn->getColumn());
        }

        return $this->result;
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

        $resultTemp = [];
            
        foreach ($this->result as $rowNumber => $row) {
            foreach ($row as $columnName => $columnValue) {
                $resultTemp[$rowNumber][$columnName] = $columnValue;
            }
        }

        $sortTemp = [];

        foreach ($this->query->getOrderByColumns() as $orderBy) {
            $sortTemp[] = array_column($resultTemp, $orderBy->getColumn());
            $sortTemp[] = $orderBy->getSortingConst();
            $sortTemp[] = SORT_REGULAR;
        }
            
        $sortTemp[] = &$resultTemp;
        $sortRes   = array_multisort(...$sortTemp);

        return $this->result = $resultTemp;
    }

    /**
     * @return Row[]
     */
    private function createRows()
    {
        $columnsTemp = array_merge($this->query->getSelectedColumns(), $this->columns);

        $columns = [];

        /**
         * @var SelectedColumn $column
         */
        foreach ($columnsTemp as $column) {
            if ($column->hasAlias()) {
                $columns[] = $column->getAlias()->getTo();
            } else {
                $columns[] = $column->getColumn();
            }
        }

        $rows = [];

        foreach ($this->result as $row) {
            $rowObject = new Row([]);
            
            foreach ($row as $column => $value) {
                if (in_array($column, $columns, true)) {
                    $rowObject->get()->{$column} = $value;
                }
            }

            $rows[] = $rowObject;
        }
        
        return $rows;
    }

    /**
     * @param JoinedTable $table
     *
     * @return array
     */
    private function joinedTableAliases(JoinedTable $table)
    {
        if ($table->getTable() instanceof Table) {
            $rows = $table->getTable()->getRows();
        } elseif ($table->getTable() instanceof Query) {
            $rows = $table->getTable()->run()->getQuery()->getResult();
        }

        if (!$table->hasAlias()) {
            return $rows;
        }

        $result = [];

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
        if ($this->query->getTable() instanceof Table) {
            $rows = $this->query->getTable()->getRows();
        } elseif ($this->query->getTable() instanceof Query) {
            $rows = $this->query->getTable()->run()->getQuery()->getResult();
        }

        if (!$this->query->hasTableAlias()) {
            return $rows;
        }

        $result = [];

        foreach ($rows as $rowNumber => $row) {
            foreach ($row as $columnName => $columnValue) {
                $result[$rowNumber][$this->query->getTableAlias()->getTo() . Alias::DELIMITER . $columnName] = $columnValue;
                $result[$rowNumber][$columnName] = $columnValue;
            }
        }

        return $result;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function union()
    {
        if (!$this->query->hasUnionQuery()) {
            return $this->result;
        }

        $unionTemporary = [];

        foreach ($this->query->getUnionQueries() as $unionQuery) {
            $runResult = $unionQuery->run();

            $count = $this->query->getSelectedColumnsCount();
            $count += count($this->columns);

            if (count($runResult->getColumns()) !== $count) {
                throw new Exception('Unioned query has not the same count of columns as a main query.');
            }

            $unionTemporary = array_merge($unionTemporary, $runResult->getQuery()->getResult());
        }

        $result = $unionTemporary;

        foreach ($unionTemporary as $row2) {
            $found = false;

            foreach ($this->result as $row) {
                if ($row === $row2) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $result[] = $row2;
            }
        }

        return $this->result = $result;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function unionAll()
    {
        if (!$this->query->hasUnionAllQuery()) {
            return $this->result;
        }

        foreach ($this->query->getUnionAllQueries() as $unionAllQuery) {
            $runResult = $unionAllQuery->run();

            $count = $this->query->getSelectedColumnsCount();
            $count += count($this->columns);

            if (count($runResult->getColumns()) !== $count) {
                throw new Exception('Unioned query has not the same count of columns as a main query.');
            }

            $this->result = array_merge($this->result, $runResult->getQuery()->getResult());
        }

        return $this->result;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function intersect()
    {
        if (!$this->query->hasIntersectQuery()) {
            return $this->result;
        }

        $intersectTemporary = [];

        foreach ($this->query->getIntersectQueries() as $intersectQuery) {
            $runResult = $intersectQuery->run();

            $count = $this->query->getSelectedColumnsCount();
            $count += count($this->columns);

            if (count($runResult->getColumns()) !== $count) {
                throw new Exception('Unioned query has not the same count of columns as a main query.');
            }

            $intersectTemporary = array_merge($intersectTemporary, $runResult->getQuery()->getResult());
        }

        $result = [];

        foreach ($this->result as $row) {
            if (in_array($row, $intersectTemporary, true)) {
                $result[] = $row;
            }
        }

        return $this->result = $result;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function except()
    {
        if (!$this->query->hasExceptQuery()) {
            return $this->result;
        }

        $exceptTemporary = [];

        foreach ($this->query->getExceptQueries() as $exceptQuery) {
            $runResult = $exceptQuery->run();

            $count = $this->query->getSelectedColumnsCount();
            $count += count($this->columns);

            if (count($runResult->getColumns()) !== $count) {
                throw new Exception('Unioned query has not the same count of columns as a main query.');
            }

            $exceptTemporary = array_merge($exceptTemporary, $runResult->getQuery()->getResult());
        }

        $result = [];

        foreach ($this->result as $row) {
            if (!in_array($row, $exceptTemporary, true)) {
                $result[] = $row;
            }
        }

        return $this->result = $result;
    }
}
