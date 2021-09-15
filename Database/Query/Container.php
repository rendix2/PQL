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


use PQL\Query\Builder\Select;
use PQL\Query\Runner\AggregateFunctionsPostGroupByExecutor;
use PQL\Query\Runner\AggregateFunctionsPreGroupByExecutor;
use PQL\Query\Runner\ConditionExecutor;
use PQL\Query\Runner\DistinctExecutor;
use PQL\Query\Runner\ExceptExecutor;
use PQL\Query\Runner\FunctionsExecutor;
use PQL\Query\Runner\GroupByExecutor;
use PQL\Query\Runner\HavingExecutor;
use PQL\Query\Runner\IntersectExecutor;
use PQL\Query\Runner\JoinExecutor;
use PQL\Query\Runner\Optimizer;
use PQL\Query\Runner\OrderByExecutor;
use PQL\Query\Runner\UnionAllExecutor;
use PQL\Query\Runner\UnionExecutor;
use PQL\Query\Runner\WhereExecutor;

class Container
{
    private Select $query;

    private GroupByExecutor $groupByExecutor;

    public function __construct(Select $query)
    {
        $this->query = $query;

        $this->groupByExecutor = new GroupByExecutor($query);
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function getConditionExecutor() : ConditionExecutor
    {
        return new ConditionExecutor();
    }

    public function getOptimizer() : Optimizer
    {
        return new Optimizer($this->getConditionExecutor());
    }

    public function getWhereExecutor() : WhereExecutor
    {
        return new WhereExecutor($this->query, $this->getConditionExecutor());
    }

    public function getJoinExecutor() : JoinExecutor
    {
        return new JoinExecutor($this->query, $this->getOptimizer(), $this->getWhereExecutor());
    }

    public function getAggregateFunctionsPreGroupByExecutor() : AggregateFunctionsPreGroupByExecutor
    {
        return new AggregateFunctionsPreGroupByExecutor($this->query, $this->getGroupByExecutor());
    }

    public function getAggregateFunctionsPostGroupByExecutor() : AggregateFunctionsPostGroupByExecutor
    {
        return new AggregateFunctionsPostGroupByExecutor($this->query, $this->getGroupByExecutor());
    }

    public function getGroupByExecutor() : GroupByExecutor
    {
        return $this->groupByExecutor;
    }

    public function getHavingExecutor() : HavingExecutor
    {
        return new HavingExecutor($this->query, $this->getGroupByExecutor(), $this->getConditionExecutor());
    }

    public function getOrderByExecutor() : OrderByExecutor
    {
        return new OrderByExecutor($this->query);
    }

    public function getFunctionsExecutor() : FunctionsExecutor
    {
        return new FunctionsExecutor($this->query);
    }

    public function getIntersectExecutor() : IntersectExecutor
    {
        return new IntersectExecutor($this->query);
    }

    public function getExceptExecutor() : ExceptExecutor
    {
        return new ExceptExecutor($this->query);
    }

    public function getUnionExecutor() : UnionExecutor
    {
        return new UnionExecutor($this->query);
    }

    public function getUnionAllExecutor() : UnionAllExecutor
    {
        return new UnionAllExecutor($this->query);
    }

    public function getDistinctExecutor() : DistinctExecutor
    {
        return new DistinctExecutor($this->query);
    }
}