<?php
/** @testCase */

namespace PQL\Tests;

use Nette\PhpGenerator\PhpFile;
use PQL\Database;
use PQL\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Query\Builder\Expressions\Column;
use PQL\Query\Builder\Expressions\FunctionExpression;
use PQL\Query\Builder\Expressions\HavingCondition;
use PQL\Query\Builder\Expressions\IntegerValue;
use PQL\Query\Builder\Expressions\JoinConditionExpression;
use PQL\Query\Builder\Expressions\Minus;
use PQL\Query\Builder\Expressions\Operator;
use PQL\Query\Builder\Expressions\Plus;
use PQL\Query\Builder\Expressions\StringValue;
use PQL\Query\Builder\Expressions\TableExpression;
use PQL\Query\Builder\Expressions\WhereCondition;
use PQL\Query\Builder\Select as SelectBuilder;
use PQL\Server;
use Tester\Assert;
use Tester\TestCase;

require_once 'bootstrap.php';

/**
 *
 * Created by PhpStorm.
 * Filename: Select.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 23:28
 */
class SelectTest extends TestCase
{

    public static string $nameSpace = '\\PQL\\Tests\\InputData\\';

    private PrepareSelect $prepareSelect;

    public function __construct()
    {
        $this->prepareSelect = new PrepareSelect();
    }

    public function testColumnsFrom(): void
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testDistinctColumn()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testInnerJoinTableOnCondition() : void
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testCrossJoin() : void
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testLeftJoinTableOnCondition() : void
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testSingleArgumentFunction()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testExpressions()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

/*    public function testAloneExpressions()
    {
        $query = clone $this->query;

        $query->select(
            new Plus(
                new IntegerValue(1),
                new Plus(
                    new IntegerValue(2),
                    new Minus(
                        new IntegerValue(3),
                        new IntegerValue(4),
                    )
                )
            )
        );

        return $query;
    }*/

    public function testWhereSingleCondition()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testWhereDualCondition()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testSingleGroupBy()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testAggregateFunctionWithoutGroupBy()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testAggregateFunctionWithGroupBy()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testSingleHaving()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testDualHaving()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testSingleOrderByColumnAsc()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testSingleOrderByColumnDesc()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testSingleOrderByFunctionAsc()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        dump($rows);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        dump($testInputRows);

        Assert::same($testInputRows, $rows);
    }

    public function testSingleOrderByAggregateFunctionAsc()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testLimit()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testOffset()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }

    public function testLimitOffset()
    {
        $rows = call_user_func([$this->prepareSelect, __FUNCTION__]);

        $className = static::$nameSpace . ucfirst(__FUNCTION__ );
        $testInputRows = call_user_func([new $className, 'getData']);

        Assert::same($testInputRows, $rows);
    }
}

// Spuštění testovacích metod
(new SelectTest)->run();