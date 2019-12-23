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
     * @var array $columns
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
        $this->groupedByData = null;
        $this->optimizer = null;
        $this->countGroupedByData = null;

        parent::__destruct();
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
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
        $this->columns[] = $functionColumnName;

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
     * @param string $function_column_name
     */
    private function addGroupedFunctionDataIntoResult($column, array $groupedByResult, $function_column_name)
    {
        foreach ($this->result as &$row) {
            $row[$function_column_name] = $groupedByResult[$column][$row[$column]];
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

            if ($functionName === FunctionPql::SUM) {
                if ($this->countGroupedByData) {
                    $this->columns[] = $functionColumnName;
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

            if ($functionName === FunctionPql::COUNT) {
                if ($this->countGroupedByData) {
                    $this->columns[] = $functionColumnName;
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

            if ($functionName === FunctionPql::AVERAGE) {
                if ($this->countGroupedByData) {
                    $this->columns[] = $functionColumnName;
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

            if ($functionName === FunctionPql::MIN) {
                if ($this->countGroupedByData) {
                    $this->columns[] = $functionColumnName;
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

            if ($functionName === FunctionPql::MAX) {
                if ($this->countGroupedByData) {
                    $this->columns[] = $functionColumnName;
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

            if ($functionName === FunctionPql::MEDIAN) {
                if ($this->countGroupedByData) {
                    $this->columns[] = $functionColumnName;
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

    private function doGroupBy(array $rows, $column)
    {
        $tmp = [];

        foreach ($rows as $row) {
            $tmp[$column][$row[$column]][] = $row;
        }

        $this->groupedByData = $tmp;
        $this->countGroupedByData = count($this->groupedByData);

        $result = [];

        foreach ($tmp[$column] as $groupRows) {
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
        
        foreach ($this->query->getGroupBy() as $groupByColumn) {
            $this->result = $this->doGroupBy($this->result, $groupByColumn);
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
        $columns = array_merge($this->query->getSelectedColumns(), $this->columns);
        
        foreach ($this->result as $row) {
            $newRow = new Row([]);
            
            foreach ($row as $column => $value) {
                if (in_array($column, $columns, true)) {
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

