<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Scheduer.php
 * User: Tomáš Babický
 * Date: 18.09.2021
 * Time: 0:02
 */

namespace PQL\Database\Query\Scheduler;

use Exception;
use PQL\Database\Query\Builder\Expressions\JoinCondition;
use PQL\Database\Query\Builder\Expressions\QueryExpression;
use PQL\Database\Query\Builder\Expressions\TableExpression;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Executor\Join\HashJoin;
use PQL\Database\Query\Executor\Join\NestedLoopJoin;
use PQL\Query\Container;

/**
 * Class Scheduler
 *
 * @package PQL\Database\Query\Scheduler
 */
class Scheduler
{
    public const INDEX = 'i';

    public const TABLE = 't';

    private Container $container;

    private int $countColumns;

    private int $countCrossJoins;

    private int $countExceptClause;

    private int $countFunctions;

    private int $countGroupBy;

    private int $countHavingClause;

    private int $countInnerJoins;

    private int $countIntersectClause;

    private int $countLeftJoins;

    private int $countMathBinaryOperators;

    private int $countOrderBy;

    private int $countRightJoins;

    private int $countUnionAllClause;

    private int $countUnionClause;

    private int $countValues;

    private int $countWhere;

    /**
     * @var bool $hasMathBinaryOperators
     */
    private bool $hasMathBinaryOperators;

    private array $innerJoins;

    private array $crossJoins;

    private array $leftJoins;

    private array $rightJoins;

    /**
     * @var bool $fromClauseIsEmpty
     */
    private bool $fromClauseIsEmpty;

    /**
     * @var bool $fromClauseIsQuery
     */
    private bool $fromClauseIsQuery;

    /**
     * @var bool $fromClauseIsView
     */
    private bool $fromClauseIsView;

    /**
     * @var bool $fromClauseIsTable
     */
    private bool $fromClauseIsTable;
    /**
     * @var bool $hasColumns
     */
    private bool $hasColumns;

    private bool $hasCrossJoins;

    private bool $hasDistinct;

    private bool $hasExceptClause;

    /**
     * @var bool
     */
    private bool $hasFromClause;

    /**
     * @var bool $hasFunctions
     */
    private bool $hasFunctions;

    /**
     * @var bool $hasGroupByClause
     */
    private bool $hasGroupByClause;

    private bool $hasHavingClause;

    private bool $hasInnerJoins;

    private bool $hasInteresectClause;

    private bool $hasJoins;

    private bool $hasLeftJoins;

    private bool $hasLimit;
    /**
     * @var bool $hasOffset
     */
    private bool $hasOffset;

    private bool $hasOrderByClause;

    private bool $hasRightJoins;

    private bool $hasUnionAllClause;

    private bool $hasUnionClause;

    private bool $hasValues;

    private bool $hasWhereClause;

    private bool $whereByPrimaryKey;

    private SelectBuilder $query;

    /**
     * @param SelectBuilder $query
     * @param Container     $container
     */
    public function __construct(SelectBuilder $query, Container $container)
    {
        $this->query = $query;

        $this->container = $container;

        $this->fromClauseIsTable = false;
        $this->fromClauseIsView = false;
        $this->fromClauseIsQuery = false;
        $this->fromClauseIsEmpty = false;

        $this->countColumns = 0;
        $this->countInnerJoins = 0;
        $this->countCrossJoins = 0;
        $this->countLeftJoins = 0;
        $this->countRightJoins = 0;
        $this->countFunctions = 0;
        $this->countMathBinaryOperators = 0;
        $this->countValues = 0;
        $this->countWhere = 0;
        $this->countGroupBy = 0;
        $this->countHavingClause = 0;
        $this->countOrderBy = 0;

        $this->countIntersectClause = 0;
        $this->countExceptClause = 0;
        $this->countUnionClause = 0;
        $this->countUnionAllClause = 0;

        $this->hasColumns = false;
        $this->hasDistinct = false;
        $this->hasFromClause = false;
        $this->hasInnerJoins = false;
        $this->hasCrossJoins = false;
        $this->hasLeftJoins = false;
        $this->hasRightJoins = false;
        $this->hasJoins = false;
        $this->hasFunctions = false;
        $this->hasMathBinaryOperators = false;
        $this->hasValues = false;
        $this->hasWhereClause = false;
        $this->hasGroupByClause = false;
        $this->hasHavingClause = false;
        $this->hasOrderByClause = false;
        $this->hasLimit = false;
        $this->hasOffset = false;

        $this->hasInteresectClause = false;
        $this->hasExceptClause = false;
        $this->hasUnionClause = false;
        $this->hasUnionAllClause = false;

        $this->innerJoins = [];
        $this->crossJoins = [];
        $this->leftJoins = [];
        $this->rightJoins = [];
    }

    public function run() : void
    {
        $this->checkColumns();
        $this->checkDistinct();
        $this->checkFrom();
        $this->checkInnerJoins();
        $this->checkCrossJoins();
        $this->checkLeftJoins();
        $this->checkRightJoins();
        $this->checkJoins();
        $this->checkFunctions();
        $this->checkValues();
        $this->checkMathBinaryOperators();
        $this->checkWhere();
        $this->checkGroupBy();
        $this->checkHaving();
        $this->checkOrderBy();
        $this->checkLimit();
        $this->checkOffset();

        $this->checkIntersect();
        $this->checkExcept();
        $this->checkUnion();
        $this->checkUnionAll();

        $this->whereByPrimaryKey = $this->checkGettingByPrimaryKey();
    }

    private function checkColumns() : void
    {
        $this->countColumns = count($this->query->getColumns());

        if ($this->countColumns) {
            $this->hasColumns = true;
        }
    }

    /**
     * @throws Exception
     */
    private function checkFrom() : void
    {
        $fromClause = $this->query->getFromClause();

        if ($fromClause instanceof TableExpression) {
            $this->fromClauseIsTable = true;
            $this->hasFromClause = true;
        } elseif ($fromClause instanceof QueryExpression) {
            $this->fromClauseIsQuery = true;
            $this->hasFromClause = true;
        } elseif ($fromClause === null) {
            $this->fromClauseIsEmpty = true;
            $this->hasFromClause = false;
        } else {
            throw new Exception('Unknown FROM');
        }
    }

    private function checkInnerJoins() : void
    {
        $this->countInnerJoins = count($this->query->getInnerJoinedTables());

        if ($this->countInnerJoins) {
            $this->hasInnerJoins = true;
        }

        foreach ($this->query->getInnerJoinedTables() as $i => $innerJoinedTable) {
            if ($innerJoinedTable->getJoinExpression() instanceof TableExpression) {
                $table = $innerJoinedTable->getJoinExpression()->getTable();
                $primaryKey = $table->getMetaData()->primaryTableKey;

                foreach ($innerJoinedTable->getJoinConditions() as $j => $condition) {
                    $left = $condition->getLeft()->evaluate();
                    $operator = $condition->getOperator()->getOperator();
                    $right = $condition->getRight()?->evaluate();

                    if ($operator === '=') {
                        $joinStrategy = new HashJoin();
                    } else {
                        $joinStrategy = new NestedLoopJoin($this->container->getJoinConditionExecutor());
                    }

                    if ($left === $primaryKey || $right === $primaryKey) {
                        $dataSource = static::INDEX;
                    } else {
                        $dataSource = static::TABLE;
                    }

                    $this->innerJoins[$i][$j] = new JoinScheduler($dataSource, $joinStrategy);
                }
            }
        }
    }

    private function checkCrossJoins() : void
    {
        $this->countCrossJoins = count($this->query->getCrossJoinedTables());

        if ($this->countCrossJoins) {
            $this->hasCrossJoins = true;
        }

        foreach ($this->query->getCrossJoinedTables() as $table) {
            $this->crossJoins[] = static::TABLE;
        }
    }

    private function checkLeftJoins() : void
    {
        $this->countLeftJoins = count($this->query->getLeftJoinedTables());

        if ($this->countLeftJoins) {
            $this->hasLeftJoins = true;
        }

        foreach ($this->query->getLeftJoinedTables() as $i => $leftJoinedTable) {
            if ($leftJoinedTable->getJoinExpression() instanceof TableExpression) {
                $table = $leftJoinedTable->getJoinExpression()->getTable();
                $primaryKey = $table->getMetaData()->primaryTableKey;

                foreach ($leftJoinedTable->getJoinConditions() as $j => $condition) {
                    $left = $condition->getLeft()->evaluate();
                    $operator = $condition->getOperator()->getOperator();
                    $right = $condition->getRight()?->evaluate();

                    if ($operator === '=') {
                        $joinStrategy = new HashJoin();
                    } else {
                        $joinStrategy = new NestedLoopJoin($this->container->getJoinConditionExecutor());
                    }

                    if ($leftJoinedTable->getJoinConditions() instanceof JoinCondition && ($left === $primaryKey || $right === $primaryKey)) {
                        $dataSource = static::INDEX;
                    } else {
                        $dataSource = static::TABLE;
                    }

                    $this->leftJoins[$i][$j] = new JoinScheduler($dataSource, $joinStrategy);
                }
            }
        }
    }

    private function checkRightJoins() : void
    {
        $this->countRightJoins = count($this->query->getRightJoinedTables());

        if ($this->countRightJoins) {
            $this->hasRightJoins = true;
        }

        foreach ($this->query->getRightJoinedTables() as $i => $rightJoinedTable) {
            if ($rightJoinedTable->getJoinExpression() instanceof TableExpression) {
                $table = $rightJoinedTable->getJoinExpression()->getTable();
                $primaryKey = $table->getMetaData()->primaryTableKey;

                foreach ($rightJoinedTable->getJoinConditions() as $j => $condition) {

                    $left = $condition->getLeft()->evaluate();
                    $operator = $condition->getOperator()->getOperator();
                    $right = $condition->getRight()?->evaluate();

                    if ($operator === '=') {
                        $joinStrategy = new HashJoin();
                    } else {
                        $joinStrategy = new NestedLoopJoin($this->container->getJoinConditionExecutor());
                    }

                    if ($left === $primaryKey || $right === $primaryKey) {
                        $dataSource = static::INDEX;
                    } else {
                        $dataSource = static::TABLE;
                    }

                    $this->rightJoins[$i][$j] = new JoinScheduler($dataSource, $joinStrategy);
                }
            }
        }
    }

    private function checkJoins() : void
    {
        if ($this->hasInnerJoins || $this->hasCrossJoins || $this->hasLeftJoins || $this->hasRightJoins) {
            $this->hasJoins = true;
        }
    }

    private function checkFunctions() : void
    {
        $this->countFunctions = count($this->query->getFunctions());

        if ($this->countFunctions) {
            $this->hasFunctions = true;
        }
    }

    private function checkValues() : void
    {
        $this->countValues = count($this->query->getValues());

        if ($this->countValues) {
            $this->hasValues = true;
        }
    }

    private function checkMathBinaryOperators() : void
    {
        $this->countMathBinaryOperators = count($this->query->getMathBinaryOperators());

        if ($this->countMathBinaryOperators) {
            $this->hasMathBinaryOperators = true;
        }
    }

    private function checkWhere() : void
    {
        $this->countWhere = count($this->query->getWhereConditions());

        if ($this->countWhere) {
            $this->hasWhereClause = true;
        }
    }

    private function checkGroupBy() : void
    {
        $this->countGroupBy = count($this->query->getGroupByColumns());

        if ($this->countGroupBy) {
            $this->hasGroupByClause = true;
        }
    }

    private function checkHaving() : void
    {
        $this->countHavingClause = count($this->query->getHavingConditions());

        if ($this->countHavingClause) {
            $this->hasHavingClause = true;
        }
    }

    private function checkOrderBy() : void
    {
        $this->countOrderBy = count($this->query->getOrderByColumns());

        if ($this->countOrderBy) {
            $this->hasOrderByClause = true;
        }
    }

    private function checkLimit() : void
    {
        $this->hasLimit = $this->query->getLimit() !== null;
    }

    private function checkOffset() : void
    {
        $this->hasOffset = $this->query->getOffset() !== null;
    }

    private function checkDistinct() : void
    {
        $this->hasDistinct = $this->query->getDistinct() !== null;
    }

    private function checkIntersect() : void
    {
        $this->countIntersectClause = count($this->query->getIntersected());

        if ($this->countIntersectClause) {
            $this->hasInteresectClause = true;
        }
    }

    private function checkExcept() : void
    {
        $this->countExceptClause = count($this->query->getExceptedQueries());

        if ($this->countExceptClause) {
            $this->hasExceptClause = true;
        }
    }

    private function checkUnion() : void
    {
        $this->countUnionClause = count($this->query->getUnionedQueries());

        if ($this->countUnionClause) {
            $this->hasUnionClause = true;
        }
    }

    private function checkUnionAll() : void
    {
        $this->countUnionAllClause = count($this->query->getUnionedAllQueries());

        if ($this->countUnionAllClause) {
            $this->hasUnionAllClause = true;
        }
    }

    private function checkGettingByPrimaryKey() : bool
    {
        if (!$this->hasWhereClause) {
            return false;
        }

        if ($this->countWhere !== 1) {
            return false;
        }

        if (!$this->fromClauseIsTable) {
            return false;
        }

        $columns = $this->query->getFromClause()->getTable()->getColumns();
        $columnName = $this->query->getWhereConditions()[0]->getLeft()->evaluate();

        foreach ($columns as $column) {
            if ($column->primary && $columnName === $column->tableName) {
                return true;
            }
        }

        return false;
    }

    //// GETTERS

    /**
     * @return JoinScheduler[]
     */
    public function getInnerJoins() : array
    {
        return $this->innerJoins;
    }

    /**
     * @return JoinScheduler[]
     */
    public function getLeftJoins() : array
    {
        return $this->leftJoins;
    }

    /**
     * @return JoinScheduler[]
     */
    public function getRightJoins() : array
    {
        return $this->rightJoins;
    }

    /**
     * @return int
     */
    public function getCountColumns() : int
    {
        return $this->countColumns;
    }

    /**
     * @return bool
     */
    public function hasMathBinaryOperators() : bool
    {
        return $this->hasMathBinaryOperators;
    }

    /**
     * @return bool
     */
    public function hasLeftJoins() : bool
    {
        return $this->hasLeftJoins;
    }

    /**
     * @return bool
     */
    public function hasInnerJoins() : bool
    {
        return $this->hasInnerJoins;
    }

    /**
     * @return bool
     */
    public function hasRightJoins() : bool
    {
        return $this->hasRightJoins;
    }

    /**
     * @return bool
     */
    public function hasHavingClause() : bool
    {
        return $this->hasHavingClause;
    }

    /**
     * @return bool
     */
    public function hasIntersectClause() : bool
    {
        return $this->hasInteresectClause;
    }

    /**
     * @return bool
     */
    public function hasUnionAllClause() : bool
    {
        return $this->hasUnionAllClause;
    }

    /**
     * @return bool
     */
    public function hasUnionClause() : bool
    {
        return $this->hasUnionClause;
    }

    /**
     * @return bool
     */
    public function hasExceptClause() : bool
    {
        return $this->hasExceptClause;
    }

    /**
     * @return bool
     */
    public function hasWhereClause() : bool
    {
        return $this->hasWhereClause;
    }

    /**
     * @return bool
     */
    public function hasDistinct() : bool
    {
        return $this->hasDistinct;
    }

    public function hasOrderByClause()
    {
        return $this->hasOrderByClause;
    }
}
