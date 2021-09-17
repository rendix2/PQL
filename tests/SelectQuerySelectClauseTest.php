<?php
/**
 *
 * Created by PhpStorm.
 * Filename: SelectQuerySelectClauseTest.php
 * User: Tomáš Babický
 * Date: 17.09.2021
 * Time: 16:34
 *
 * @testCase
 */

namespace PQL\Tests;

use PQL\Tests\InputData\TestColumnsFrom;
use PQL\Tests\InputData\TestDistinctColumn;
use PQL\Tests\InputData\TestDistinctFunctionColumn;
use PQL\Tests\InputData\TestExpressions;
use PQL\Tests\InputData\TestSingleArgumentFunction;
use Tester\Assert;
use Tester\TestCase;

require_once 'bootstrap.php';

class SelectQuerySelectClauseTest extends TestCase
{
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


}

require_once 'bootstrap.php';

(new SelectQuerySelectClauseTest())->run();