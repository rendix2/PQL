<?php

namespace pql\QueryExecutor;

use Exception;
use Netpromotion\Profiler\Profiler;
use pql\Alias;
use pql\Condition;
use pql\JoinedTable;
use pql\QueryBuilder\Query;
use pql\QueryBuilder\Select\AggregateFunction;
use pql\QueryBuilder\Select\Expression;
use pql\QueryBuilder\Select\IMathExpression;
use pql\QueryBuilder\Select\ISelectExpression;
use pql\QueryBuilder\Select\StandardFunction;
use pql\QueryBuilder\SelectQuery as SelectBuilder;
use pql\QueryExecutor\AggregateFunctions\Average;
use pql\QueryExecutor\AggregateFunctions\Count;
use pql\QueryExecutor\AggregateFunctions\Max;
use pql\QueryExecutor\AggregateFunctions\Median;
use pql\QueryExecutor\AggregateFunctions\Min;
use pql\QueryExecutor\AggregateFunctions\Sum;
use pql\QueryExecutor\Functions\NumberFormat;
use pql\QueryExecutor\Joins\HashJoin;
use pql\QueryExecutor\Joins\NestedLoopJoin;
use pql\QueryExecutor\Joins\SortMergeJoin;
use pql\QueryResult\TableResult;
use pql\QueryRow\TableRow;
use pql\SelectedColumn;
use pql\Table;

/**
 * Class Select
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class SelectQuery implements IQueryExecutor
{
    use Limit;

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
     * @var SelectBuilder $query
     */
    private $query;

    /**
     * @var array $result
     */
    private $result;

    /**
     * @var bool $hasExpression
     */
    private $hasExpression;

    /**
     * Select constructor.
     *
     * @param SelectBuilder $query
     */
    public function __construct(SelectBuilder $query)
    {
        $this->optimizer = new Optimizer($query);
        $this->columns   = [];

        $this->query = $query;

        $this->hasExpression = false;
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

        $this->query = null;
        $this->result = null;
    }

    /**
     * @return SelectBuilder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return SelectedColumn[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getGroupedByData()
    {
        return $this->groupedByData;
    }

    /**
     * @return TableRow[]
     */
    public function run()
    {
        $this->checkColumns();

        Profiler::start('getRows');
        $this->result = $this->fromTableAliases();
        Profiler::finish('getRows');

        //bdump($this->result, '$this->result SET');

        Profiler::start('DISTINCT');
        $this->result = $this->distinct();
        Profiler::finish('DISTINCT');

        //bdump($this->result, '$this->result DISTINCT');

        Profiler::start('whereForFromClause');
        $this->whereForFromClause();
        Profiler::finish('whereForFromClause');

        //bdump($this->result, '$this->result whereForFromClause');

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

        Profiler::start('valueFunctions');
        $this->valueFunctions();
        Profiler::finish('valueFunctions');

        //bdump($this->result, '$this->result VALUE FUNCTIONS');

        Profiler::start('functions');
        $this->aggregateFunctions();
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
        $rows = $this->createRows();
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
            if ($innerJoinedTable->getTable() instanceof Table) {
                foreach ($innerJoinedTable->getTable()->getColumns() as $column) {
                    $columns[] = $column->getName();

                    if ($innerJoinedTable->hasAlias()) {
                        $columns[] = $innerJoinedTable->getAlias()->getTo() . Alias::DELIMITER . $column->getName();
                    }
                }
            } elseif ($innerJoinedTable->getTable() instanceof Query) {
                $tmpColumns = $innerJoinedTable->getTable()->getQuery()->getSelectedColumns();

                foreach ($tmpColumns as $column) {
                    $columns[] = $column->getColumn();
                }
            } else {
                throw new Exception('Unknown inner join input.');
            }
        }

        foreach ($this->query->getLeftJoinedTables() as $leftJoinedTable) {
            if ($leftJoinedTable->getTable() instanceof Table) {
                foreach ($leftJoinedTable->getTable()->getColumns() as $column) {
                    $columns[] = $column->getName();

                    if ($leftJoinedTable->hasAlias()) {
                        $columns[] = $leftJoinedTable->getAlias()->getTo() . Alias::DELIMITER . $column->getName();
                    }
                }
            } elseif ($leftJoinedTable->getTable() instanceof Query) {
                $tmpColumns = $leftJoinedTable->getTable()->getQuery()->getSelectedColumns();

                foreach ($tmpColumns as $column) {
                    $columns[] = $column->getColumn();
                }
            } else {
                throw new Exception('Unknown left joined input.');
            }
        }

        foreach ($this->query->getRightJoinedTables() as $rightJoinedTable) {
            if ($rightJoinedTable->getTable() instanceof Table) {
                foreach ($rightJoinedTable->getTable()->getColumns() as $column) {
                    $columns[] = $column->getName();

                    if ($rightJoinedTable->hasAlias()) {
                        $columns[] = $rightJoinedTable->getAlias()->getTo() . Alias::DELIMITER . $column->getName();
                    }
                }
            } elseif ($rightJoinedTable->getTable() instanceof Query) {
                $tmpColumns = $rightJoinedTable->getTable()->getQuery()->getSelectedColumns();

                foreach ($tmpColumns as $column) {
                    $columns[] = $column->getColumn();
                }
            } else {
                throw new Exception('Unknown right join input.');
            }
        }

        foreach ($this->query->getFullJoinedTables() as $fullJoinedTable) {
            if ($fullJoinedTable->getTable() instanceof Table) {
                foreach ($fullJoinedTable->getTable()->getColumns() as $column) {
                    $columns[] = $column->getName();

                    if ($fullJoinedTable->hasAlias()) {
                        $columns[] = $fullJoinedTable->getAlias()->getTo() . Alias::DELIMITER . $column->getName();
                    }
                }
            } elseif ($fullJoinedTable->getTable() instanceof Query) {
                $tmpColumns = $fullJoinedTable->getTable()->getQuery()->getSelectedColumns();

                foreach ($tmpColumns as $column) {
                    $columns[] = $column->getColumn();
                }
            } else {
                throw new Exception('Unknown input for full join.');
            }
        }

        foreach ($this->query->getCrossJoinedTables() as $crossJoinedTable) {
            if ($crossJoinedTable->getTable() instanceof Table) {
                foreach ($crossJoinedTable->getTable()->getColumns() as $column) {
                    $columns[] = $column->getName();

                    if ($crossJoinedTable->hasAlias()) {
                        $columns[] = $crossJoinedTable->getAlias()->getTo() . Alias::DELIMITER . $column->getName();
                    }
                }
            } elseif ($crossJoinedTable->getTable() instanceof Query) {
                $tmpColumns = $crossJoinedTable->getTable()->getQuery()->getSelectedColumns();

                foreach ($tmpColumns as $column) {
                    $columns[] = $column->getColumn();
                }
            } else {
                throw new Exception('Unknown input for cross join.');
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
            $selectedColumns = [];

            // get columns from this query
            foreach ($this->query->getSelectedColumns() as $column) {
                $selectedColumns[] = $column->getColumn();
            }

            $result = $this->query->getTable()->getQuery();

            if ($result instanceof TableResult) {
                $query = $result->getQuery();

                if ($query instanceof self) {
                    foreach ($query->getColumns() as $column) {
                        $selectedColumns[] = $column->getColumn();
                    }
                }
            }

            $columns = array_merge($columns, $selectedColumns);
        }

        foreach ($this->query->getSelectedColumns() as $column) {
            if ($column->getExpression() instanceof IMathExpression || $column->getExpression() instanceof Expression) {
                $this->hasExpression = true;
                continue;
            } elseif ($column->getExpression() instanceof StandardFunction) {
                if (!in_array($column->getExpression()->getColumn(), $columns, true)) {
                    throw new Exception(sprintf('Selected column "%s" does not exists.', $column->getColumn()));
                }
            } elseif ($column->getExpression() instanceof AggregateFunction) {
                if (!in_array($column->getExpression()->getColumn(), $columns, true)) {
                    throw new Exception(sprintf('Selected column "%s" does not exists.', $column->getColumn()));
                }
            } elseif (is_string($column->getColumn())) {
                if (!in_array($column->getColumn(), $columns, true)) {
                    throw new Exception(sprintf('Selected column "%s" does not exists.', $column->getColumn()));
                }
            }
        }
    }

    /**
     * @param ISelectExpression $expression
     * @param mixed       $functionResult
     */
    private function addFunctionIntoResult(ISelectExpression $expression, $functionResult)
    {
        if ($this->query->getSelectedColumns()) {
            foreach ($this->result as &$row) {
                $row[$expression->evaluate()] = $functionResult;
            }

            unset($row);
        } else {
            $this->result = [0 => [$expression->evaluate() => $functionResult]];
        }
    }

    /**
     * add desired data into result
     *
     * @param string $column
     * @param array  $groupedByResult
     * @param string $functionColumnName
     */
    public function addGroupedFunctionDataIntoResult($column, array $groupedByResult, $functionColumnName)
    {
        //$this->columns[] = new SelectedColumn($functionColumnName);

        foreach ($this->result as &$row) {
            $row[$functionColumnName] = $groupedByResult[$column][$row[$column]];
        }

        unset($row);
    }

    /**
     * @return array
     */
    private function distinct()
    {
        if ($this->query->getDistinctColumn() === null) {
            return $this->result;
        }

        $resultTemp = [];

        foreach ($this->result as $row) {
            $column = $row[$this->query->getDistinctColumn()->getColumn()];

            $resultTemp[$column] = $row;
        }

        return array_values($resultTemp);
    }

    private function valueFunctions()
    {
        if(!$this->query->hasFunctions()) {
            return $this->result;
        }

        foreach ($this->result as $column => &$row) {
            foreach ($this->query->getSelectedColumns() as $selectedColumn) {
                $expression = $selectedColumn->getExpression();

                if ($expression instanceof StandardFunction) {
                    switch ($expression->getName()) {
                        case NumberFormat::FUNCTION_NAME:
                            $valueFunction = new NumberFormat();

                            $row[(string) $expression->evaluate()] = $valueFunction->run($row[$expression->getColumn()], $expression->getParams());
                            break;
                    }
                }
            }
        }

        return $this->result;
    }

    /**
     *
     */
    private function aggregateFunctions()
    {
        if (!$this->query->hasAggregateFunctions()) {
            return $this->result;
        }

        $functions = new AggregateFunctions($this->result);

        foreach ($this->query->getSelectedColumns() as $column) {
            if ($column->getExpression() instanceof AggregateFunction) {
                $functionColumnName = $column->getExpression()->evaluate();

                switch ($column->getExpression()->getName()) {
                    case AggregateFunction::SUM:
                        if ($this->groupedByDataCount) {
                            $aggregateFunction = new Sum($this);
                            $aggregateFunction->run($column, $functionColumnName);
                        } else {
                            $this->addFunctionIntoResult($column->getExpression(), $functions->sum($column));
                        }

                        break;

                    case AggregateFunction::COUNT:
                        if ($this->groupedByDataCount) {
                            $aggregateFunction = new Count($this);
                            $aggregateFunction->run($column, $functionColumnName);
                        } else {
                            $this->addFunctionIntoResult($column->getExpression(), $functions->count($column));
                        }

                        break;

                    case AggregateFunction::AVERAGE:
                        if ($this->groupedByDataCount) {
                            $aggregateFunction = new Average($this);
                            $aggregateFunction->run($column, $functionColumnName);
                        } else {
                            $this->addFunctionIntoResult($column->getExpression(), $functions->avg($column));
                        }

                        break;

                    case AggregateFunction::MIN:
                        if ($this->groupedByDataCount) {
                            $aggregateFunction = new Min($this);
                            $aggregateFunction->run($column, $functionColumnName);
                        } else {
                            $this->addFunctionIntoResult($column->getExpression(), $functions->min($column));
                        }

                        break;

                    case AggregateFunction::MAX:
                        if ($this->groupedByDataCount) {
                            $aggregateFunction = new Max($this);
                            $aggregateFunction->run($column, $functionColumnName);
                        } else {
                            $this->addFunctionIntoResult($column->getExpression(), $functions->max($column));
                        }
                        break;

                    case AggregateFunction::MEDIAN:
                        if ($this->groupedByDataCount) {
                            $aggregateFunction = new Median($this);
                            $aggregateFunction->run($column, $functionColumnName);
                        } else {
                            $this->addFunctionIntoResult($column->getExpression(), $functions->median($column));
                        }

                        break;
                }
            }
        }

        return $this->result;
    }

    /**
     * @return array|TableRow[]
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

                $orderOfTables = $this->optimizer->sayOrderOfInnerJoinedTables($this->result, $innerJoinedTableRows);

                switch ($this->optimizer->sayJoinAlgorithm($innerJoinedTable, $condition)) {
                    case Optimizer::MERGE_JOIN:
                        if ($orderOfTables === Optimizer::TABLE_A_FIRST) {
                            $this->result = SortMergeJoin::innerJoin($this->result, $innerJoinedTableRows, $condition);
                        } else {
                            $this->result = SortMergeJoin::innerJoin($innerJoinedTableRows, $this->result, $condition);
                        }
                        break;

                    case Optimizer::HASH_JOIN:
                        if ($orderOfTables === Optimizer::TABLE_A_FIRST) {
                            $this->result = HashJoin::innerJoin($this->result, $innerJoinedTableRows, $condition);
                        } else {
                            $this->result = HashJoin::innerJoin($innerJoinedTableRows, $this->result, $condition);
                        }
                        break;

                    case Optimizer::NESTED_LOOP:
                        if ($orderOfTables === Optimizer::TABLE_A_FIRST) {
                            $this->result = NestedLoopJoin::innerJoin($this->result, $innerJoinedTableRows, $condition);
                        } else {
                            $this->result = NestedLoopJoin::innerJoin($innerJoinedTableRows, $this->result, $condition);
                        }
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
     * @return array|TableRow[]
     * @throws Exception
     */
    private function leftJoin()
    {
        if (!$this->query->hasLeftJoinedTable()) {
            return $this->result;
        }

        foreach ($this->query->getLeftJoinedTables() as $leftJoinedTable) {
            foreach ($leftJoinedTable->getOnConditions() as $condition) {
                $leftJoinedTableRows = $this->joinedTableAliases($leftJoinedTable);

                switch ($this->optimizer->sayJoinAlgorithm($leftJoinedTable, $condition)) {
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

                switch ($this->optimizer->sayJoinAlgorithm($rightJoinedTable, $condition)) {
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

                switch ($this->optimizer->sayJoinAlgorithm($fullJoinedTable, $condition)) {
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
     * @return array|TableRow[]
     * @throws Exception
     */
    private function where()
    {
        if (!$this->query->hasWhereCondition()) {
            return $this->result;
        }

        if ($this->optimizer->sayIfCanOptimizeWhere()) {
            $this->result = $this->doWhere($this->result, $this->query->getWhereConditions()[0]);
        } else {
            foreach ($this->query->getWhereConditions() as $whereCondition) {
                $this->result = $this->doWhere($this->result, $whereCondition);
            }
        }

        return $this->result;
    }

    /**
     * @return array
     */
    private function whereForFromClause()
    {
        if (!$this->query->hasWhereCondition()) {
            return $this->result;
        }

        $table = $this->query->getTable();

        while ($table instanceof Query) {
            $table = $table->getTable();
        }

        foreach ($this->query->getWhereConditions() as $key => $whereCondition) {
            // column
            if (is_string($whereCondition->getColumn())) {
                if (strpos($whereCondition->getColumn(),Alias::DELIMITER) === false && $table->columnExists($whereCondition->getColumn())) {
                    $this->result = $this->doWhere($this->result, $whereCondition);

                    $this->query->removeWhereCondition($key);
                } elseif ($this->query->hasTableAlias()) {
                    $exploded = explode(Alias::DELIMITER, $whereCondition->getColumn());

                    if (count($exploded) === 2) {
                        list($alias, $column) = $exploded;

                        if ($alias === $this->query->getTableAlias()->getTo() && $table->columnExists($column)) {
                            $this->result = $this->doWhere($this->result, $whereCondition);

                            $this->query->removeWhereCondition($key);
                        }
                    }
                }
            }

            // value
            if (is_string($whereCondition->getValue())) {
                if (strpos($whereCondition->getValue(),Alias::DELIMITER) === false && $table->columnExists($whereCondition->getValue())) {
                    $this->result = $this->doWhere($this->result, $whereCondition);

                    $this->query->removeWhereCondition($key);
                } elseif ($this->query->hasTableAlias()) {
                    $exploded = explode(Alias::DELIMITER, $whereCondition->getValue());

                    if (count($exploded) === 2) {
                        list($alias, $column) = $exploded;

                        if ($alias === $this->query->getTableAlias()->getTo() && $table->columnExists($column)) {
                            $this->result = $this->doWhere($this->result, $whereCondition);

                            $this->query->removeWhereCondition($key);
                        }
                    }
                }
            }
        }

        return $this->result;
    }

    /**
     * @param array  $rows
     * @param string $column
     *
     * @return array
     * @throws Exception
     */
    private function doGroupBy(array $rows, $column)
    {
        $groupByTemp = [];

        foreach ($rows as $row) {
            $groupByTemp[$column][$row[$column]][] = $row;
        }

        // put into aggregated rows missing selected columns
        foreach ($this->query->getAggregateFunctions() as $function) {
            foreach ($groupByTemp as &$groupByColumn) {
                foreach ($groupByColumn as $value => &$groupedRows) {
                    $aggregateFunctions = new AggregateFunctions($groupedRows);

                    switch ($function->getName()) {
                        case AggregateFunction::SUM:
                            $functionResult = $aggregateFunctions->sum($function->getName());
                            break;
                        case AggregateFunction::COUNT:
                            $functionResult = $aggregateFunctions->count($function->getName());
                            break;
                        case AggregateFunction::MAX:
                            $functionResult = $aggregateFunctions->max($function->getName());
                            break;
                        case AggregateFunction::MIN:
                            $functionResult = $aggregateFunctions->min($function->getName());
                            break;
                        case AggregateFunction::AVERAGE:
                            $functionResult = $aggregateFunctions->avg($function->getName());
                            break;
                        case AggregateFunction::MEDIAN:
                            $functionResult = $aggregateFunctions->median($function->getName());
                            break;
                        default:
                            $message = sprintf('Unknown aggregate function "%s".', $function->getName());

                            throw new Exception($message);
                    }

                    foreach ($groupedRows as &$groupedRow) {
                        $groupedRow[$function->evaluate()] = $functionResult;
                    }
                }
            }
        }

        unset($groupByColumn, $groupedRows, $groupedRow);

        // calculate needed functions
        foreach ($this->query->getHavingConditions() as $havingCondition) {
            $smallFunctionName = mb_strtolower($havingCondition->getColumn()->getName());
            $firstParam = $havingCondition->getColumn()->getColumn();

            if ($havingCondition->getColumn() instanceof AggregateFunction) {
                foreach ($groupByTemp as &$groupByColumn) {
                    foreach ($groupByColumn as $value => &$groupedRows) {
                        $aggregateFunctions = new AggregateFunctions($groupedRows);

                        switch ($smallFunctionName) {
                            case AggregateFunction::SUM:
                                $functionResult = $aggregateFunctions->sum($firstParam);
                                break;
                            case AggregateFunction::COUNT:
                                $functionResult = $aggregateFunctions->count($firstParam);
                                break;
                            case AggregateFunction::MAX:
                                $functionResult = $aggregateFunctions->max($firstParam);
                                break;
                            case AggregateFunction::MIN:
                                $functionResult = $aggregateFunctions->min($firstParam);
                                break;
                            case AggregateFunction::AVERAGE:
                                $functionResult = $aggregateFunctions->avg($firstParam);
                                break;
                            case AggregateFunction::MEDIAN:
                                $functionResult = $aggregateFunctions->median($firstParam);
                                break;
                            default:
                                $message = sprintf('Unknown aggregate function "%s".', $smallFunctionName);

                                throw new Exception($message);
                        }

                        foreach ($groupedRows as &$groupedRow) {
                            $groupedRow[(string) $havingCondition->getColumn()->evaluate()] = $functionResult;
                        }
                    }
                }

                unset($groupByColumn, $groupedRows, $groupedRow);
            }
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
     * @return array|TableRow[]
     */
    private function groupBy()
    {
        if (!$this->query->hasGroupBy()) {
            return $this->result;
        }
        
        foreach ($this->query->getGroupByColumns() as $groupByColumn) {
            $this->result = $this->doGroupBy($this->result, $groupByColumn->getColumn());
        }

        return $this->result;
    }

    /**
     * @param array     $rows
     * @param array     $havingResult
     * @param Condition $condition
     *
     * @return array
     */
    private function havingRowsHelper(array $rows, array $havingResult, Condition $condition)
    {
        if (count($rows)) {
            $havingResultTemp = [];

            foreach ($rows as $row) {
                if (ConditionHelper::havingCondition($condition, $row[(string)$condition->getColumn()])) {
                    $havingResultTemp[] = $row;
                }
            }

            return $havingResultTemp;
        }

        return $havingResult;
    }

    /**
     * @param array     $rows
     * @param Condition $condition
     *
     * @return array
     * @throws Exception
     */
    private function doHaving(array $rows, Condition $condition)
    {
        $havingResult = [];

        $smallFunctionName = mb_strtolower($condition->getColumn()->getName());

        if ($condition->getColumn() instanceof AggregateFunction) {
            $functionParameter = $condition->getColumn()->getColumn();

            foreach ($this->groupedByData as $groupByColumn => $groupByValues) {
                foreach ($groupByValues as $groupedRows) {
                    $functions = new AggregateFunctions($groupedRows);

                    switch ($smallFunctionName) {
                        case AggregateFunction::SUM:
                            $sum = $functions->sum($functionParameter);

                            if (ConditionHelper::havingCondition($condition, $sum)) {
                                $havingResult[] = $groupedRows[0];
                            }

                            $havingResult = $this->havingRowsHelper($rows, $havingResult, $condition);
                            break;
                        case AggregateFunction::COUNT:
                            $count = $functions->count($functionParameter);

                            if (ConditionHelper::havingCondition($condition, $count)) {
                                $havingResult[] = $groupedRows[0];
                            }

                            $havingResult = $this->havingRowsHelper($rows, $havingResult, $condition);
                            break;
                        case AggregateFunction::AVERAGE:
                            $avg = $functions->avg($functionParameter);

                            if (ConditionHelper::havingCondition($condition, $avg)) {
                                foreach ($groupedRows as $groupedRow) {
                                    if (ConditionHelper::havingCondition($condition, $groupedRow[$functionParameter])) {
                                        $havingResult[] = $groupedRows[0];
                                    }
                                }
                            }

                            $havingResult = $this->havingRowsHelper($rows, $havingResult, $condition);
                            break;
                        case AggregateFunction::MIN:
                            $min = $functions->min($functionParameter);

                            if (ConditionHelper::havingCondition($condition, $min)) {
                                foreach ($groupedRows as $groupedRow) {
                                    if (ConditionHelper::havingCondition($condition, $groupedRow[$functionParameter])) {
                                        $havingResult[] = $groupedRow;
                                    }
                                }
                            }

                            $havingResult = $this->havingRowsHelper($rows, $havingResult, $condition);
                            break;
                        case AggregateFunction::MAX:
                            $max = $functions->max($functionParameter);

                            if (ConditionHelper::havingCondition($condition, $max)) {
                                foreach ($groupedRows as $groupedRow) {
                                    if (ConditionHelper::havingCondition($condition, $groupedRow[$functionParameter])) {
                                        $havingResult[] = $groupedRow;
                                    }
                                }
                            }

                            $havingResult = $this->havingRowsHelper($rows, $havingResult, $condition);
                            break;
                        case AggregateFunction::MEDIAN:
                            $median = $functions->median($functionParameter);

                            if (ConditionHelper::havingCondition($condition, $median)) {
                                foreach ($groupedRows as $groupedRow) {
                                    if (ConditionHelper::havingCondition($condition, $groupedRow[$functionParameter])) {
                                        $havingResult[] = $groupedRow;
                                    }
                                }
                            }

                            $havingResult = $this->havingRowsHelper($rows, $havingResult, $condition);
                            break;
                        default:
                            $message = sprintf('Unknown aggregate function "%s".', $smallFunctionName);

                            throw new Exception($message);
                    }
                }
            }
        }

        return $havingResult;
    }

    /**
     * @return array|TableRow[]
     */
    private function having()
    {
        if (!$this->query->hasHavingCondition()) {
            return $this->result;
        }

        $having = [];

        foreach ($this->query->getHavingConditions() as $havingCondition) {
            $having = $this->doHaving($having, $havingCondition);
        }

        return $this->result = $having;
    }

    /**
     * @return array|TableRow[]
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
        $sortRes    = array_multisort(...$sortTemp);

        return $this->result = $resultTemp;
    }

    /**
     * @return TableRow[]
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
            $rowObject = new TableRow([]);
            
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
     * @throws Exception
     */
    private function joinedTableAliases(JoinedTable $table)
    {
        if ($table->getTable() instanceof Table) {
            $rows = $table->getTable()->getRows();
        } elseif ($table->getTable() instanceof Query) {
            $rows = $table->getTable()->run()->getQuery()->getResult();
        } else {
            throw new Exception('Unknown input in FROM clause.');
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
     * @return array|TableRow[]
     * @throws Exception
     */
    private function fromTableAliases()
    {
        if ($this->query->getTable() instanceof Table) {
            $rows = $this->query->getTable()->getRows();
        } elseif ($this->query->getTable() instanceof Query) {
            $rows = $this->query->getTable()->run()->getQuery()->getResult();
        } elseif ($this->hasExpression) {
            $result = [];

            foreach ($this->getQuery()->getSelectedColumns() as $selectedColumn) {
                $result[] = [$selectedColumn->getExpression()->evaluate() => $selectedColumn->getExpression()->result()];
            }

            return $result;
        } else {
            throw new Exception('Unknown input in FROM clause.');
        }

        if (!$this->query->hasTableAlias()) {
            return $rows;
        }

        $result = [];

        foreach ($rows as $rowNumber => $row) {
            foreach ($row as $columnName => $columnValue) {
                $aliasColumnName = $this->query->getTableAlias()->getTo() . Alias::DELIMITER . $columnName;

                $result[$rowNumber][$aliasColumnName] = $result[$rowNumber][$columnName] = $columnValue;
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
