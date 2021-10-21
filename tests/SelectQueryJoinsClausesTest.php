<?php
/** @testCase */

namespace PQL\Tests;

use PQL\Bootstrap;
use PQL\Tests\InputData\TestCrossJoin;
use PQL\Tests\InputData\TestInnerJoinTableOnCondition;
use PQL\Tests\InputData\TestLeftJoinTableOnCondition;
use PQL\Tests\InputData\TestLeftJoinTableOnConditionGreater;
use PQL\Tests\InputData\TestLeftJoinTableOnConditionGreaterEquals;
use PQL\Tests\InputData\TestLeftJoinTableOnConditionInArray;
use PQL\Tests\InputData\TestLeftJoinTableOnConditionNotEquals;
use PQL\Tests\InputData\TestLeftJoinTableOnConditionNotEquals2;
use PQL\Tests\InputData\TestLeftJoinTableOnConditionNotInArray;
use PQL\Tests\InputData\TestLeftJoinTableOnConditionSmaller;
use Tester\Assert;
use Tester\TestCase;

require_once '../Bootstrap.php';
$bootstrap = new Bootstrap();
$bootstrap->test();

class SelectQueryJoinsClausesTest extends TestCase
{

    private SelectTestQueryFactory $selectTestQueryFactory;

    public function __construct()
    {
        $this->selectTestQueryFactory = new SelectTestQueryFactory();
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

    public function testLeftJoinTableOnConditionGreater() : void
    {
        $query = $this->selectTestQueryFactory->testLeftJoinTableOnConditionGreater();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestLeftJoinTableOnConditionGreater();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLeftJoinTableOnConditionGreaterEquals() : void
    {
        $query = $this->selectTestQueryFactory->testLeftJoinTableOnConditionGreaterEquals();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestLeftJoinTableOnConditionGreaterEquals();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLeftJoinTableOnConditionSmaller() : void
    {
        $query = $this->selectTestQueryFactory->testLeftJoinTableOnConditionSmaller();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestLeftJoinTableOnConditionSmaller();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLeftJoinTableOnConditionEquals() : void
    {
        $query = $this->selectTestQueryFactory->testLeftJoinTableOnCondition();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestLeftJoinTableOnCondition();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLeftJoinTableOnConditionNotEquals() : void
    {
        $query = $this->selectTestQueryFactory->testLeftJoinTableOnConditionNotEquals();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestLeftJoinTableOnConditionNotEquals();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLeftJoinTableOnConditionNotEquals2() : void
    {
        $query = $this->selectTestQueryFactory->testLeftJoinTableOnConditionNotEquals2();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestLeftJoinTableOnConditionNotEquals2();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLeftJoinTableOnConditionInArray() : void
    {
        $query = $this->selectTestQueryFactory->testLeftJoinTableOnConditionInArray();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestLeftJoinTableOnConditionInArray();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testLeftJoinTableOnConditionNotInArray() : void
    {
        $query = $this->selectTestQueryFactory->testLeftJoinTableOnConditionNotInArray();
        $queryRows = ArrayHelper::createArray($query->execute());

        $dataObj = new TestLeftJoinTableOnConditionNotInArray();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }
}

(new SelectQueryJoinsClausesTest)->run();