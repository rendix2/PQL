<?php
/** @testCase */

namespace PQL\Tests;

use PQL\Bootstrap;
use PQL\Tests\InputData\TestAggregateFunctionWithGroupBy;
use PQL\Tests\InputData\TestAggregateFunctionWithoutGroupBy;
use PQL\Tests\InputData\TestSingleGroupBy;
use Tester\Assert;
use Tester\TestCase;

require_once '../Bootstrap.php';
$bootstrap = new Bootstrap();
$bootstrap->test();

class SelectQueryGroupByClauseTest extends TestCase
{
    private SelectTestQueryFactory $selectTestQueryFactory;

    public function __construct()
    {
        $this->selectTestQueryFactory = new SelectTestQueryFactory();
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
}

(new SelectQueryGroupByClauseTest())->run();