<?php
/** @testCase */

namespace PQL\Tests;

use PQL\Tests\InputData\TestAggregateFunctionWithGroupBy;
use PQL\Tests\InputData\TestAggregateFunctionWithoutGroupBy;
use PQL\Tests\InputData\TestColumnsFrom;
use PQL\Tests\InputData\TestCrossJoin;
use PQL\Tests\InputData\TestDistinctColumn;
use PQL\Tests\InputData\TestDualHaving;
use PQL\Tests\InputData\TestExpressions;
use PQL\Tests\InputData\TestInnerJoinTableOnCondition;
use PQL\Tests\InputData\TestLeftJoinTableOnCondition;
use PQL\Tests\InputData\TestLimit;
use PQL\Tests\InputData\TestLimitOffset;
use PQL\Tests\InputData\TestOffset;
use PQL\Tests\InputData\TestSingleArgumentFunction;
use PQL\Tests\InputData\TestSingleGroupBy;
use PQL\Tests\InputData\TestSingleHaving;
use PQL\Tests\InputData\TestSingleOrderByAggregateFunctionAsc;
use PQL\Tests\InputData\TestSingleOrderByColumnAsc;
use PQL\Tests\InputData\TestSingleOrderByColumnDesc;
use PQL\Tests\InputData\TestSingleOrderByFunctionAsc;
use PQL\Tests\InputData\TestWhereDualCondition;
use PQL\Tests\InputData\TestWhereSingleCondition;
use Tester\Assert;
use Tester\TestCase;

require_once 'bootstrap.php';

/**
 *
 * Created by PhpStorm.
 * Filename: SelectTest.php
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

    public function testColumnsFrom() : void
    {
        $queryRows = $this->prepareSelect->testColumnsFrom();

        $dataObj = new TestColumnsFrom();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testDistinctColumn() : void
    {
        $queryRows = $this->prepareSelect->testDistinctColumn();

        $dataObj = new TestDistinctColumn();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testInnerJoinTableOnCondition() : void
    {
        $queryRows = $this->prepareSelect->testInnerJoinTableOnCondition();

        $dataObj = new TestInnerJoinTableOnCondition();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testCrossJoin() : void
    {
        $queryRows = $this->prepareSelect->testCrossJoin();

        $dataObj = new TestCrossJoin();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLeftJoinTableOnCondition() : void
    {
        $queryRows = $this->prepareSelect->testLeftJoinTableOnCondition();

        $dataObj = new TestLeftJoinTableOnCondition();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleArgumentFunction() : void
    {
        $queryRows = $this->prepareSelect->testSingleArgumentFunction();

        $dataObj = new TestSingleArgumentFunction();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testExpressions() : void
    {
        $queryRows = $this->prepareSelect->testExpressions();

        $dataObj = new TestExpressions();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
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

    public function testWhereSingleCondition() : void
    {
        $queryRows = $this->prepareSelect->testWhereSingleCondition();

        $dataObj = new TestWhereSingleCondition();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereDualCondition() : void
    {
        $queryRows = $this->prepareSelect->testWhereDualCondition();

        $dataObj = new TestWhereDualCondition();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleGroupBy() : void
    {
        $queryRows = $this->prepareSelect->testSingleGroupBy();

        $dataObj = new TestSingleGroupBy();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testAggregateFunctionWithoutGroupBy() : void
    {
        $queryRows = $this->prepareSelect->testAggregateFunctionWithoutGroupBy();

        $dataObj = new TestAggregateFunctionWithoutGroupBy();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testAggregateFunctionWithGroupBy() : void
    {
        $queryRows = $this->prepareSelect->testAggregateFunctionWithGroupBy();

        $dataObj = new TestAggregateFunctionWithGroupBy();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleHaving() : void
    {
        $queryRows = $this->prepareSelect->testSingleHaving();

        $dataObj = new TestSingleHaving();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testDualHaving() : void
    {
        $queryRows = $this->prepareSelect->testDualHaving();

        $dataObj = new TestDualHaving();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleOrderByColumnAsc() : void
    {
        $queryRows = $this->prepareSelect->testSingleOrderByColumnAsc();

        $dataObj = new TestSingleOrderByColumnAsc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleOrderByColumnDesc() : void
    {
        $queryRows = $this->prepareSelect->testSingleOrderByColumnDesc();

        $dataObj = new TestSingleOrderByColumnDesc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleOrderByFunctionAsc() : void
    {
        $queryRows = $this->prepareSelect->testSingleOrderByFunctionAsc();

        $dataObj = new TestSingleOrderByFunctionAsc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleOrderByAggregateFunctionAsc() : void
    {
        $queryRows = $this->prepareSelect->testSingleOrderByAggregateFunctionAsc();

        $dataObj = new TestSingleOrderByAggregateFunctionAsc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLimit() : void
    {
        $queryRows = $this->prepareSelect->testLimit();

        $dataObj = new TestLimit();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testOffset() : void
    {
        $queryRows = $this->prepareSelect->testOffset();

        $dataObj = new TestOffset();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLimitOffset() : void
    {
        $queryRows = $this->prepareSelect->testLimitOffset();

        $dataObj = new TestLimitOffset();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }
}

// Spuštění testovacích metod
(new SelectTest)->run();