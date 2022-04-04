<?php
/** @testCase */

use PQL\Bootstrap;
use PQL\Query\ArrayHelper;
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
use PQL\Tests\SelectTestQueryFactory;
use Tester\Assert;
use Tester\TestCase;

require_once '../Bootstrap.php';
$bootstrap = new Bootstrap();
$bootstrap->test();

class SelectQueryWhereClauseTest extends TestCase
{

    private SelectTestQueryFactory $selectTestQueryFactory;

    public function __construct()
    {
        $this->selectTestQueryFactory = new SelectTestQueryFactory();
    }

    public function testWhereSingleCondition() : void
    {
        $query = $this->selectTestQueryFactory->testWhereSingleCondition();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereSingleCondition();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereDualCondition() : void
    {
        $query = $this->selectTestQueryFactory->testWhereDualCondition();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereDualCondition();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereEquals() : void
    {
        $query = $this->selectTestQueryFactory->testWhereEquals();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereEquals();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereNotEquals1() : void
    {
        $query = $this->selectTestQueryFactory->testWhereNotEquals1();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereNotEquals1();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereNotEquals2() : void
    {
        $query = $this->selectTestQueryFactory->testWhereNotEquals2();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereNotEquals2();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereGreater() : void
    {
        $query = $this->selectTestQueryFactory->testWhereGreater();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereGreater();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereLess() : void
    {
        $query = $this->selectTestQueryFactory->testWhereLess();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereLess();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereLessInc() : void
    {
        $query = $this->selectTestQueryFactory->testWhereLessInc();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereLessInc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereGreaterInc() : void
    {
        $query = $this->selectTestQueryFactory->testWhereGreaterInc();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereGreaterInc();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereIn() : void
    {
        $query = $this->selectTestQueryFactory->testWhereIn();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereIn();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereNotIn() : void
    {
        $query = $this->selectTestQueryFactory->testWhereNotIn();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereNotIn();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereIsNull() : void
    {
        $query = $this->selectTestQueryFactory->testWhereIsNull();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereIsNull();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereIsNotNull() : void
    {
        $query = $this->selectTestQueryFactory->testWhereIsNotNull();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereIsNotNull();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereBetween() : void
    {
        $query = $this->selectTestQueryFactory->testWhereBetween();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereBetween();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testWhereBetweenInclusive() : void
    {
        $query = $this->selectTestQueryFactory->testWhereBetweenInclusive();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestWhereBetweenInclusive();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

}

(new SelectQueryWhereClauseTest())->run();