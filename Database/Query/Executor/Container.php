<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Container.php
 * User: Tomáš Babický
 * Date: 05.09.2021
 * Time: 0:43
 */

namespace PQL\Query;


use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Executor\AggregateFunctionsPostGroupByExecutor;
use PQL\Database\Query\Executor\AggregateFunctionsPreGroupByExecutor;
use PQL\Database\Query\Executor\CheckExecutor;
use PQL\Database\Query\Executor\DistinctExecutor;
use PQL\Database\Query\Executor\ExceptExecutor;
use PQL\Database\Query\Executor\FunctionsExecutor;
use PQL\Database\Query\Executor\GroupByExecutor;
use PQL\Database\Query\Executor\HavingExecutor;
use PQL\Database\Query\Executor\IntersectExecutor;
use PQL\Database\Query\Executor\Join\NestedLoopJoin;
use PQL\Database\Query\Executor\JoinHelper;
use PQL\Database\Query\Executor\MathExecutor;
use PQL\Database\Query\Executor\OrderByExecutor;
use PQL\Database\Query\Executor\Select\CrossJoinExecutor;
use PQL\Database\Query\Executor\Select\InnerJoinExecutor;
use PQL\Database\Query\Executor\Select\LeftJoinExecutor;
use PQL\Database\Query\Executor\UnionAllExecutor;
use PQL\Database\Query\Executor\UnionExecutor;
use PQL\Database\Query\Executor\WhereExecutor;
use PQL\Database\Query\Optimizer\InnerJoinOptimizer;
use PQL\Database\Query\Optimizer\LeftJoinOptimizer;
use PQL\Database\Query\Scheduler\Scheduler;
use PQL\Database\Query\Select\Condition\HavingConditionExecutor;
use PQL\Database\Query\Select\Condition\JoinConditionExecutor;
use PQL\Database\Query\Select\Condition\WhereConditionExecutor;

/**
 * Class Container
 *
 * @package PQL\Query
 */
class Container
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @var GroupByExecutor $groupByExecutor
     */
    private GroupByExecutor $groupByExecutor;

    /**
     * @var Scheduler $scheduler
     */
    private Scheduler $scheduler;

    /**
     * @param SelectBuilder $query
     */
    public function __construct(SelectBuilder $query)
    {
        $this->query = $query;

        $this->groupByExecutor = new GroupByExecutor($query);

        $this->scheduler = new Scheduler($query, $this);
        $this->scheduler->run();
    }

    /**
     *
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return InnerJoinOptimizer
     */
    public function getInnerJoinOptimizer() : InnerJoinOptimizer
    {
        return new InnerJoinOptimizer();
    }

    /**
     * @return LeftJoinOptimizer
     */
    public function getLeftJoinOptimizer() : LeftJoinOptimizer
    {
        return new LeftJoinOptimizer();
    }

    /**
     * @return WhereExecutor
     */
    public function getWhereExecutor() : WhereExecutor
    {
        return new WhereExecutor(
            $this->query,
            $this->scheduler,
            $this->getWhereConditionExecutor(),
        );
    }

    /**
     * @return JoinHelper
     */
    public function getJoinExecutor() : JoinHelper
    {
        return new JoinHelper();
    }

    /**
     * @return WhereConditionExecutor
     */
    public function getWhereConditionExecutor() : WhereConditionExecutor
    {
        return new WhereConditionExecutor();
    }

    /**
     * @return InnerJoinExecutor
     */
    public function getInnerJoinExecutor() : InnerJoinExecutor
    {
        return new InnerJoinExecutor(
            $this->query,
            $this->getScheduler(),
            $this->getWhereExecutor(),
            $this->getInnerJoinOptimizer(),
            $this->getJoinHelper(),
        );
    }

    /**
     * @return CrossJoinExecutor
     */
    public function getCrossJoinExecutor() : CrossJoinExecutor
    {
        return new CrossJoinExecutor($this->query, $this->getNestedLoopJoin());
    }

    /**
     * @return LeftJoinExecutor
     */
    public function getLeftJoinExecutor() : LeftJoinExecutor
    {
        return new LeftJoinExecutor(
            $this->query,
            $this->getScheduler(),
            $this->getLeftJoinOptimizer(),
            $this->getWhereExecutor(),
            $this->getJoinHelper(),
        );
    }

    /**
     * @return AggregateFunctionsPreGroupByExecutor
     */
    public function getAggregateFunctionsPreGroupByExecutor() : AggregateFunctionsPreGroupByExecutor
    {
        return new AggregateFunctionsPreGroupByExecutor($this->query);
    }

    /**
     * @return AggregateFunctionsPostGroupByExecutor
     */
    public function getAggregateFunctionsPostGroupByExecutor() : AggregateFunctionsPostGroupByExecutor
    {
        return new AggregateFunctionsPostGroupByExecutor($this->query, $this->getGroupByExecutor());
    }

    /**
     * @return GroupByExecutor
     */
    public function getGroupByExecutor() : GroupByExecutor
    {
        return $this->groupByExecutor;
    }

    /**
     * @return HavingConditionExecutor
     */
    public function getHavingConditionExecutor() : HavingConditionExecutor
    {
        return new HavingConditionExecutor();
    }

    /**
     * @return HavingExecutor
     */
    public function getHavingExecutor() : HavingExecutor
    {
        return new HavingExecutor(
            $this->query,
            $this->scheduler,
            $this->getGroupByExecutor(),
            $this->getHavingConditionExecutor()
        );
    }

    /**
     * @return OrderByExecutor
     */
    public function getOrderByExecutor() : OrderByExecutor
    {
        return new OrderByExecutor($this->query, $this->scheduler);
    }

    /**
     * @return FunctionsExecutor
     */
    public function getFunctionsExecutor() : FunctionsExecutor
    {
        return new FunctionsExecutor($this->query);
    }

    /**
     * @return IntersectExecutor
     */
    public function getIntersectExecutor() : IntersectExecutor
    {
        return new IntersectExecutor($this->query, $this->scheduler);
    }

    /**
     * @return ExceptExecutor
     */
    public function getExceptExecutor() : ExceptExecutor
    {
        return new ExceptExecutor($this->query, $this->scheduler);
    }

    /**
     * @return UnionExecutor
     */
    public function getUnionExecutor() : UnionExecutor
    {
        return new UnionExecutor($this->query, $this->scheduler);
    }

    /**
     * @return UnionAllExecutor
     */
    public function getUnionAllExecutor() : UnionAllExecutor
    {
        return new UnionAllExecutor($this->query, $this->scheduler);
    }

    /**
     * @return DistinctExecutor
     */
    public function getDistinctExecutor() : DistinctExecutor
    {
        return new DistinctExecutor($this->query);
    }

    /**
     * @return Scheduler
     */
    public function getScheduler() : Scheduler
    {
        return $this->scheduler;
    }

    /**
     * @return NestedLoopJoin
     */
    public function getNestedLoopJoin() : NestedLoopJoin
    {
        return new NestedLoopJoin($this->getJoinConditionExecutor());
    }

    /**
     * @return JoinHelper
     */
    public function getJoinHelper() : JoinHelper
    {
        return new JoinHelper();
    }

    /**
     * @return JoinConditionExecutor
     */
    public function getJoinConditionExecutor() : JoinConditionExecutor
    {
        return new JoinConditionExecutor();
    }

    /**
     * @return MathExecutor
     */
    public function getMathExecutor() : MathExecutor
    {
        return new MathExecutor($this->query, $this->scheduler);
    }

    /**
     * @return CheckExecutor
     */
    public function getCheckExecutor() : CheckExecutor
    {
        return new CheckExecutor($this->query, $this->scheduler);
    }
}