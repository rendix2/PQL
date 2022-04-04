<?php
/** @testCase */

namespace PQL\Tests;

use PQL\Bootstrap;
use PQL\Query\ArrayHelper;
use PQL\Tests\InputData\TestDualHaving;
use PQL\Tests\InputData\TestHavingBetween;
use PQL\Tests\InputData\TestHavingBetweenInclusive;
use PQL\Tests\InputData\TestHavingEquals;
use PQL\Tests\InputData\TestHavingIn;
use PQL\Tests\InputData\TestHavingLargerThan;
use PQL\Tests\InputData\TestHavingLargerThanEquals;
use PQL\Tests\InputData\TestHavingMathMinus;
use PQL\Tests\InputData\TestHavingMathMinus2;
use PQL\Tests\InputData\TestHavingMathPlus;
use PQL\Tests\InputData\TestHavingMathPlus2;
use PQL\Tests\InputData\TestHavingMathPower;
use PQL\Tests\InputData\TestHavingNotEquals1;
use PQL\Tests\InputData\TestHavingNotEquals2;
use PQL\Tests\InputData\TestHavingNotIn;
use PQL\Tests\InputData\TestHavingSmallerThan;
use PQL\Tests\InputData\TestHavingSmallerThanEquals;
use PQL\Tests\InputData\TestSingleHaving;
use Tester\Assert;
use Tester\TestCase;

require_once '../Bootstrap.php';
$bootstrap = new Bootstrap();
$bootstrap->test();

class SelectQueryHavingClauseTest extends TestCase
{
    private SelectTestQueryFactory $selectTestQueryFactory;

    public function __construct()
    {
        $this->selectTestQueryFactory = new SelectTestQueryFactory();
    }

    public function testSingleHaving() : void
    {
        $query = $this->selectTestQueryFactory->testSingleHaving();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestSingleHaving();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testDualHaving() : void
    {
        $query = $this->selectTestQueryFactory->testDualHaving();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestDualHaving();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingEquals() : void
    {
        $query = $this->selectTestQueryFactory->testHavingEquals();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingEquals();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingLargerThan() : void
    {
        $query = $this->selectTestQueryFactory->testHavingLargerThan();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingLargerThan();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingLargerThanEquals() : void
    {
        $query = $this->selectTestQueryFactory->testHavingLargerThanEquals();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingLargerThanEquals();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }


    public function testHavingSmallerThan() : void
    {
        $query = $this->selectTestQueryFactory->testHavingSmallerThan();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingSmallerThan();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingSmallerThanEquals() : void
    {
        $query = $this->selectTestQueryFactory->testHavingSmallerThanEquals();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingSmallerThanEquals();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingNotEquals1() : void
    {
        $query = $this->selectTestQueryFactory->testHavingNotEquals1();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingNotEquals1();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingNotEquals2() : void
    {
        $query = $this->selectTestQueryFactory->testHavingNotEquals2();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingNotEquals2();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingIn() : void
    {
        $query = $this->selectTestQueryFactory->testHavingIn();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingIn();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingNotIn() : void
    {
        $query = $this->selectTestQueryFactory->testHavingNotIn();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingNotIn();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingBetween() : void
    {
        $query = $this->selectTestQueryFactory->testHavingBetween();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingBetween();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingBetweenInclusive() : void
    {
        $query = $this->selectTestQueryFactory->testHavingBetweenInclusive();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingBetweenInclusive();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingMathPlus() : void
    {
        $query = $this->selectTestQueryFactory->testHavingMathPlus();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingMathPlus();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingMathPlus2() : void
    {
        $query = $this->selectTestQueryFactory->testHavingMathPlus2();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingMathPlus2();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingMathMinus() : void
    {
        $query = $this->selectTestQueryFactory->testHavingMathMinus();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingMathMinus();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingMathMinus2() : void
    {
        $query = $this->selectTestQueryFactory->testHavingMathMinus2();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingMathMinus2();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }

    public function testHavingMathPower() : void
    {
        $query = $this->selectTestQueryFactory->testHavingMathPower();
        $queryRows = ArrayHelper::toArray($query->execute());

        $dataObj = new TestHavingMathPower();
        $expectedRows = $dataObj->getData();

        Assert::same($expectedRows, $queryRows);
    }
}

(new SelectQueryHavingClauseTest())->run();