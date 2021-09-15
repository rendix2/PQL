<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Select.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 1:04
 */

namespace PQL\Query\Runner;

use Exception;
use Netpromotion\Profiler\Profiler;
use PQL\Query\Builder\Select;
use PQL\Query\Container;
use stdClass;

class SelectExecutor
{
    private AggregateFunctionsPostGroupByExecutor $aggregateFunctionsPostGroupByExecutor;

    /**
     * @var stdClass[] $data
     */
    private array $data;

    /**
     * @var AggregateFunctionsPreGroupByExecutor $aggregateFunctionsPreGroupByExecutor
     */
    private AggregateFunctionsPreGroupByExecutor $aggregateFunctionsPreGroupByExecutor;

    /**
     * @var DistinctExecutor $distinctExecutor
     */
    private DistinctExecutor $distinctExecutor;

    /**
     * @var ExceptExecutor $exceptExecutor
     */
    private ExceptExecutor $exceptExecutor;

    /**
     * @var FunctionsExecutor $functionsExecutor
     */
    private FunctionsExecutor $functionsExecutor;

    /**
     * @var HavingExecutor $havingExecutor
     */
    private HavingExecutor $havingExecutor;

    /**
     * @var IntersectExecutor $intersectExecutor
     */
    private IntersectExecutor $intersectExecutor;

    /**
     * @var JoinExecutor $joinExecutor
     */
    private JoinExecutor $joinExecutor;

    /**
     * @var OrderByExecutor $orderByExecutor
     */
    private OrderByExecutor $orderByExecutor;

    /**
     * @var Select $query
     */
    private Select $query;

    /**
     * @var GroupByExecutor $groupByExecutor
     */
    private GroupByExecutor $groupByExecutor;

    /**
     * @var UnionAllExecutor $unionAllExecutor
     */
    private UnionAllExecutor $unionAllExecutor;

    /**
     * @var UnionExecutor $unionExecutor
     */
    private UnionExecutor $unionExecutor;

    /**
     * @var WhereExecutor $whereExecutor
     */
    private WhereExecutor $whereExecutor;

    public function __construct(Select $query)
    {
        $this->query = $query;

        $container = new Container($query);

        $this->whereExecutor = $container->getWhereExecutor();
        $this->joinExecutor = $container->getJoinExecutor();
        $this->aggregateFunctionsPreGroupByExecutor = $container->getAggregateFunctionsPreGroupByExecutor();
        $this->aggregateFunctionsPostGroupByExecutor = $container->getAggregateFunctionsPostGroupByExecutor();
        $this->groupByExecutor = $container->getGroupByExecutor();
        $this->havingExecutor = $container->getHavingExecutor();
        $this->orderByExecutor = $container->getOrderByExecutor();
        $this->functionsExecutor = $container->getFunctionsExecutor();

        $this->intersectExecutor = $container->getIntersectExecutor();
        $this->exceptExecutor = $container->getExceptExecutor();
        $this->unionExecutor = $container->getUnionExecutor();
        $this->unionAllExecutor = $container->getUnionAllExecutor();

        $this->distinctExecutor = $container->getDistinctExecutor();
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function run() : array
    {

        Profiler::start('checks');
        $this->checks();
        Profiler::finish('checks');

        Profiler::start('from');
        $this->from();
        Profiler::finish('from');

        Profiler::start('innerJoins');
        $this->innerJoins();
        Profiler::finish('innerJoins');

        Profiler::start('crossJoins');
        $this->crossJoins();
        Profiler::finish('crossJoins');

        Profiler::start('leftJoins');
        $this->leftJoins();
        Profiler::finish('leftJoins');

        Profiler::start('rightJoins');
        //$this->rightJoins();
        Profiler::finish('rightJoins');

        Profiler::start('functions');
        $this->functions();
        Profiler::finish('functions');

        Profiler::start('values');
        $this->values();
        Profiler::finish('values');

        Profiler::start('where');
        $this->where();
        Profiler::finish('where');

        Profiler::start('aggregateFunctionsPreGroupBy');
        $this->aggregateFunctionsPreGroupBy();
        Profiler::finish('aggregateFunctionsPreGroupBy');

        Profiler::start('groupBy');
        $this->groupBy();
        Profiler::finish('groupBy');

        Profiler::start('aggregateFunctionsPostGroupBy');
        $this->aggregateFunctionsPostGroupBy();
        Profiler::finish('aggregateFunctionsPostGroupBy');

        Profiler::start('having');
        $this->having();
        Profiler::finish('having');

        Profiler::start('orderBy');
        $this->orderBy();
        Profiler::finish('orderBy');

        Profiler::start('limit');
        $this->limit();
        Profiler::finish('limit');

        Profiler::start('columns');
        $this->columns();
        Profiler::finish('columns');

        Profiler::start('distinct');
        $this->distinct();
        Profiler::finish('distinct');

        Profiler::start('intersect');
        $this->intersect();
        Profiler::finish('intersect');

        Profiler::start('except');
        $this->except();
        Profiler::finish('except');

        Profiler::start('union');
        $this->union();
        Profiler::finish('union');

        Profiler::start('unionAll');
        $this->unionAll();
        Profiler::finish('unionAll');

        return $this->data;
    }

    private function checks() : void
    {
        if ($this->query->getDistinct()) {
            if (count($this->query->getColumns()) > 1) {
                throw new Exception('We are using Distinct and have more than one selected columns.');
            }

            if (count($this->query->getColumns()) === 1) {
                if ($this->query->getDistinct() !== $this->query->getColumns()[0]) {
                    throw new Exception('We have set Distinct column and columns. If distinct column is user, you cannot use normal columns.');
                }
            }
        }
    }

    private function from() : void
    {
        $this->data = $this->query->getFromClause()->getData();
    }

    private function innerJoins() : void
    {
        $this->data = $this->joinExecutor->innerJoins($this->data);
    }

    private function crossJoins() : void
    {
        $this->data = $this->joinExecutor->crossJoins($this->data);
    }

    private function leftJoins() : void
    {
        $this->data = $this->joinExecutor->leftJoins($this->data);
    }

    private function rightJoins() : void
    {
        //throw new NotImplementedException();
    }

    private function where() : void
    {
        $this->data = $this->whereExecutor->run($this->data);
    }

    private function aggregateFunctionsPreGroupBy() : void
    {
        $this->data = $this->aggregateFunctionsPreGroupByExecutor->run($this->data);
    }

    private function groupBy() : void
    {
        $this->data = $this->groupByExecutor->run($this->data);
    }

    private function aggregateFunctionsPostGroupBy() : void
    {
        $this->data = $this->aggregateFunctionsPostGroupByExecutor->run($this->data);
    }

    private function having() : void
    {
        $this->data = $this->havingExecutor->run($this->data);
    }

    private function orderBy() : void
    {
        $this->data = $this->orderByExecutor->run($this->data);
    }

    private function limit() : void
    {
        $this->data = array_slice($this->data, $this->query->getOffset(), $this->query->getLimit(), true);
    }

    private function functions() : void
    {
        $this->data = $this->functionsExecutor->run($this->data);
    }

    private function values() : void
    {
        foreach ($this->query->getValues() as $value) {
            foreach ($this->data as $row) {
                if ($value->hasAlias()) {
                    $row->{$value->getAlias()} = $value->evaluate();
                } else {
                    $row->{$value->print()} = $value->evaluate();
                }
            }
        }
    }

    private function columns() : void
    {
        $res = [];

        foreach ($this->query->getColumns() as $column) {
            foreach ($this->data as $key => $value) {
                if ($column->getAlias()) {
                    $columnName = $column->getAlias();
                } else {
                    $columnName = $column->print();
                }

                $val = $this->data[$key]->{$columnName};

                $res[$key][$columnName] = $val;
            }
        }

        $objectRows = [];

        foreach ($res as $row) {
            $objectRows[] = (object) $row;
        }

        $this->data = $objectRows;
    }

    private function distinct() : void
    {
        $this->data = $this->distinctExecutor->run($this->data);
    }

    private function intersect() : void
    {
        $this->data = $this->intersectExecutor->run($this->data);
    }

    private function except() : void
    {
        $this->data = $this->exceptExecutor->run($this->data);
    }

    private function union() : void
    {
        $this->data = $this->unionExecutor->run($this->data);
    }

    private function unionAll() : void
    {
        $this->data = $this->unionAllExecutor->run($this->data);
    }
}