<?php
/** @testCase */

namespace PQL\Tests;

use PQL\Tests\InputData\TestAggregateFunctionWithGroupBy;
use PQL\Tests\InputData\TestAggregateFunctionWithoutGroupBy;
use PQL\Tests\InputData\TestColumnsFrom;
use PQL\Tests\InputData\TestCrossJoin;
use PQL\Tests\InputData\TestDistinctColumn;
use PQL\Tests\InputData\TestDistinctFunctionColumn;
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
use PQL\Tests\InputData\TestWhereBetween;
use PQL\Tests\InputData\TestWhereBetweenInclusive;
use PQL\Tests\InputData\TestWhereDualCondition;
use PQL\Tests\InputData\TestWhereEquals;
use PQL\Tests\InputData\TestWhereGreater;
use PQL\Tests\InputData\TestWhereGreaterInc;
use PQL\Tests\InputData\TestWhereIn;
use PQL\Tests\InputData\TestWhereIsNotNull;
use PQL\Tests\InputData\TestWhereIsNull;
use PQL\Tests\InputData\TestWhereLess;
use PQL\Tests\InputData\TestWhereLessInc;
use PQL\Tests\InputData\TestWhereNotEquals1;
use PQL\Tests\InputData\TestWhereNotEquals2;
use PQL\Tests\InputData\TestWhereNotIn;
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

    private SelectTestQueryFactory $selectTestQueryFactory;

    public function __construct()
    {
        $this->selectTestQueryFactory = new SelectTestQueryFactory();
    }

    public function testColumnsFrom() : void
    {
        $query = $this->selectTestQueryFactory->testColumnsFrom();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestColumnsFrom();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testDistinctColumn() : void
    {
        $query = $this->selectTestQueryFactory->testDistinctColumn();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestDistinctColumn();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testDistinctFunctionColumn() : void
    {
        $query = $this->selectTestQueryFactory->testDistinctFunctionColumn();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestDistinctFunctionColumn();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testInnerJoinTableOnCondition() : void
    {
        $query = $this->selectTestQueryFactory->testInnerJoinTableOnCondition();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestInnerJoinTableOnCondition();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testCrossJoin() : void
    {
        $query = $this->selectTestQueryFactory->testCrossJoin();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestCrossJoin();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLeftJoinTableOnCondition() : void
    {
        $query = $this->selectTestQueryFactory->testLeftJoinTableOnCondition();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestLeftJoinTableOnCondition();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleArgumentFunction() : void
    {
        $query = $this->selectTestQueryFactory->testSingleArgumentFunction();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestSingleArgumentFunction();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testExpressions() : void
    {
        $query = $this->selectTestQueryFactory->testExpressions();
        $queryRows = ArrayHelper::createArray($query->execute());

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
        $query = $this->selectTestQueryFactory->testWhereSingleCondition();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereSingleCondition();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereDualCondition() : void
    {
        $query = $this->selectTestQueryFactory->testWhereDualCondition();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereDualCondition();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereEquals() : void
    {
        $query = $this->selectTestQueryFactory->testWhereEquals();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereEquals();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereNotEquals1() : void
    {
        $query = $this->selectTestQueryFactory->testWhereNotEquals1();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereNotEquals1();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereNotEquals2() : void
    {
        $query = $this->selectTestQueryFactory->testWhereNotEquals2();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereNotEquals2();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereGreater() : void
    {
        $query = $this->selectTestQueryFactory->testWhereGreater();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereGreater();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereLess() : void
    {
        $query = $this->selectTestQueryFactory->testWhereLess();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereLess();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereLessInc() : void
    {
        $query = $this->selectTestQueryFactory->testWhereLessInc();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereLessInc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereGreaterInc() : void
    {
        $query = $this->selectTestQueryFactory->testWhereGreaterInc();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereGreaterInc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereIn() : void
    {
        $query = $this->selectTestQueryFactory->testWhereIn();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereIn();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereNotIn() : void
    {
        $query = $this->selectTestQueryFactory->testWhereNotIn();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereNotIn();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereIsNull() : void
    {
        $query = $this->selectTestQueryFactory->testWhereIsNull();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereIsNull();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereIsNotNull() : void
    {
        $query = $this->selectTestQueryFactory->testWhereIsNotNull();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereIsNotNull();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereBetween() : void
    {
        $query = $this->selectTestQueryFactory->testWhereBetween();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereBetween();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereBetweenInclusive() : void
    {
        $query = $this->selectTestQueryFactory->testWhereBetweenInclusive();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestWhereBetweenInclusive();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }




    public function testSingleGroupBy() : void
    {
        $query = $this->selectTestQueryFactory->testSingleGroupBy();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestSingleGroupBy();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testAggregateFunctionWithoutGroupBy() : void
    {
        $query = $this->selectTestQueryFactory->testAggregateFunctionWithoutGroupBy();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestAggregateFunctionWithoutGroupBy();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testAggregateFunctionWithGroupBy() : void
    {
        $query = $this->selectTestQueryFactory->testAggregateFunctionWithGroupBy();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestAggregateFunctionWithGroupBy();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleHaving() : void
    {
        $query = $this->selectTestQueryFactory->testSingleHaving();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestSingleHaving();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testDualHaving() : void
    {
        $query = $this->selectTestQueryFactory->testDualHaving();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestDualHaving();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleOrderByColumnAsc() : void
    {
        $query = $this->selectTestQueryFactory->testSingleOrderByColumnAsc();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestSingleOrderByColumnAsc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleOrderByColumnDesc() : void
    {
        $query = $this->selectTestQueryFactory->testSingleOrderByColumnDesc();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestSingleOrderByColumnDesc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleOrderByFunctionAsc() : void
    {
        $query = $this->selectTestQueryFactory->testSingleOrderByFunctionAsc();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestSingleOrderByFunctionAsc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleOrderByAggregateFunctionAsc() : void
    {
        $query = $this->selectTestQueryFactory->testSingleOrderByAggregateFunctionAsc();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestSingleOrderByAggregateFunctionAsc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLimit() : void
    {
        $query = $this->selectTestQueryFactory->testLimit();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestLimit();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testOffset() : void
    {
        $query = $this->selectTestQueryFactory->testOffset();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestOffset();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLimitOffset() : void
    {
        $query = $this->selectTestQueryFactory->testLimitOffset();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestLimitOffset();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }
}

// Spuštění testovacích metod
(new SelectTest)->run();