<?php
/**
 *
 * Created by PhpStorm.
 * Filename: SelectPrinter.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 1:04
 */

namespace PQL\Database\Query\Executor;

use Exception;
use Netpromotion\Profiler\Profiler;
use PQL\Database\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Database\Query\Builder\Expressions\Column;
use PQL\Database\Query\Builder\Expressions\Division;
use PQL\Database\Query\Builder\Expressions\IMathBinaryOperator;
use PQL\Database\Query\Builder\Expressions\IMathExpression;
use PQL\Database\Query\Builder\Expressions\Minus;
use PQL\Database\Query\Builder\Expressions\Multiplication;
use PQL\Database\Query\Builder\Expressions\Plus;
use PQL\Database\Query\Builder\Expressions\Power;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Executor\Select\CrossJoinExecutor;
use PQL\Database\Query\Executor\Select\InnerJoinExecutor;
use PQL\Database\Query\Executor\Select\LeftJoinExecutor;
use PQL\Database\Query\Scheduler\Scheduler;
use PQL\Query\ArrayHelper;
use PQL\Query\Container;
use stdClass;

/**
 * Class SelectExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class SelectExecutor
{
    /**
     * @var AggregateFunctionsPostGroupByExecutor $aggregateFunctionsPostGroupByExecutor
     */
    private AggregateFunctionsPostGroupByExecutor $aggregateFunctionsPostGroupByExecutor;

    /**
     * @var CrossJoinExecutor $crossJoinExecutor
     */
    private CrossJoinExecutor $crossJoinExecutor;

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

    private InnerJoinExecutor $innerJoinExecutor;

    /**
     * @var IntersectExecutor $intersectExecutor
     */
    private IntersectExecutor $intersectExecutor;

    /**
     * @var LeftJoinExecutor $leftJoinExecutor
     */
    private LeftJoinExecutor $leftJoinExecutor;

    /**
     * @var MathExecutor $mathExecutor
     */
    private MathExecutor $mathExecutor;

    /**
     * @var OrderByExecutor $orderByExecutor
     */
    private OrderByExecutor $orderByExecutor;

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

    /**
     * @var CheckExecutor $checkExecutor
     */
    private CheckExecutor $checkExecutor;

    /**
     * @param SelectBuilder $query
     */
    public function __construct(SelectBuilder $query)
    {
        $this->query = $query;

        $container = new Container($query);

        $this->whereExecutor = $container->getWhereExecutor();

        $this->innerJoinExecutor = $container->getInnerJoinExecutor();
        $this->crossJoinExecutor = $container->getCrossJoinExecutor();
        $this->leftJoinExecutor = $container->getLeftJoinExecutor();

        $this->aggregateFunctionsPreGroupByExecutor = $container->getAggregateFunctionsPreGroupByExecutor();
        $this->aggregateFunctionsPostGroupByExecutor = $container->getAggregateFunctionsPostGroupByExecutor();
        $this->groupByExecutor = $container->getGroupByExecutor();
        $this->havingExecutor = $container->getHavingExecutor();
        $this->orderByExecutor = $container->getOrderByExecutor();
        $this->functionsExecutor = $container->getFunctionsExecutor();

        $this->mathExecutor = $container->getMathExecutor();

        $this->intersectExecutor = $container->getIntersectExecutor();
        $this->exceptExecutor = $container->getExceptExecutor();
        $this->unionExecutor = $container->getUnionExecutor();
        $this->unionAllExecutor = $container->getUnionAllExecutor();

        $this->distinctExecutor = $container->getDistinctExecutor();

        $this->scheduler = $container->getScheduler();

        $this->checkExecutor = $container->getCheckExecutor();

        $this->data = [];
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

        Profiler::start('math');
        $this->math();
        Profiler::finish('math');

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

    /**
     * @throws Exception
     */
    private function checks() : void
    {
        $this->checkExecutor->run([]);
    }

    private function from() : void
    {
        $this->data = $this->query->getFromClause()->getData();
    }

    private function innerJoins() : void
    {
        $this->data = $this->innerJoinExecutor->run($this->data);
    }

    private function crossJoins() : void
    {
        $this->data = $this->crossJoinExecutor->run($this->data);
    }

    private function leftJoins() : void
    {
        $this->data = $this->leftJoinExecutor->run($this->data);
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

        $this->data = ArrayHelper::toObject($res);
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

    private function math() : void
    {
        $this->data = $this->mathExecutor->run($this->data);
    }
}