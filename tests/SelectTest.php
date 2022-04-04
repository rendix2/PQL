<?php
/** @testCase */

namespace PQL\Tests;

use PQL\Bootstrap;
use PQL\Query\ArrayHelper;
use PQL\Tests\InputData\TestLimit;
use PQL\Tests\InputData\TestLimitOffset;
use PQL\Tests\InputData\TestOffset;
use PQL\Tests\InputData\TestSingleOrderByAggregateFunctionAsc;
use PQL\Tests\InputData\TestSingleOrderByColumnAsc;
use PQL\Tests\InputData\TestSingleOrderByColumnDesc;
use PQL\Tests\InputData\TestSingleOrderByFunctionAsc;
use Tester\Assert;
use Tester\TestCase;

require_once '../Bootstrap.php';
$bootstrap = new Bootstrap();
$bootstrap->test();

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
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestSingleOrderByColumnAsc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleOrderByColumnDesc() : void
    {
        $query = $this->selectTestQueryFactory->testSingleOrderByColumnDesc();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestSingleOrderByColumnDesc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleOrderByFunctionAsc() : void
    {
        $query = $this->selectTestQueryFactory->testSingleOrderByFunctionAsc();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestSingleOrderByFunctionAsc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testSingleOrderByAggregateFunctionAsc() : void
    {
        $query = $this->selectTestQueryFactory->testSingleOrderByAggregateFunctionAsc();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestSingleOrderByAggregateFunctionAsc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLimit() : void
    {
        $query = $this->selectTestQueryFactory->testLimit();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestLimit();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testOffset() : void
    {
        $query = $this->selectTestQueryFactory->testOffset();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestOffset();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLimitOffset() : void
    {
        $query = $this->selectTestQueryFactory->testLimitOffset();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestLimitOffset();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }
}

(new SelectTest)->run();