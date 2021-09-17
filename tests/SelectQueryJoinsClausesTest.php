<?php
/**
 *
 * Created by PhpStorm.
 * Filename: SelectQueryJoinsClausesTest.php
 * User: Tomáš Babický
 * Date: 17.09.2021
 * Time: 16:40
 *
 * @testCase
 */

namespace PQL\Tests;

use PQL\Tests\InputData\TestCrossJoin;
use PQL\Tests\InputData\TestInnerJoinTableOnCondition;
use PQL\Tests\InputData\TestLeftJoinTableOnCondition;
use Tester\Assert;
use Tester\TestCase;

require_once 'bootstrap.php';

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


}

(new SelectQueryJoinsClausesTest)->run();