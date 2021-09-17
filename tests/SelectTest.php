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

    private SelectTestQueryFactory $selectTestQueryFactory;

    public function __construct()
    {
        $this->selectTestQueryFactory = new SelectTestQueryFactory();
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

(new SelectTest)->run();