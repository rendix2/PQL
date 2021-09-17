<?php
/**
 *
 * Created by PhpStorm.
 * Filename: TestRunner.php
 * User: Tomáš Babický
 * Date: 16.09.2021
 * Time: 1:14
 */

namespace PQL;

use PQL\Query\Builder\Select;
use PQL\Tests\SelectTestQueryFactory;
use ReflectionClass;

class TestRunner
{

    public function run() : void
    {
        $selectTestQueryFactory = new SelectTestQueryFactory();
        $reflection = new ReflectionClass(new $selectTestQueryFactory());
        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $methodName = $method->getName();
            $isTestMethod = str_starts_with($methodName, 'test' );

            if ($isTestMethod) {
                $query = $selectTestQueryFactory->{$methodName}();

                echo $methodName . ':';
                $this->print($query);
            }
        }
    }

    /**
     * @param Select $query
     */
    public function print(Select $query) : void
    {
        echo $query->printQuery();
        $res = $query->execute();

        dump($res);
        echo '<br><br>';
        echo $query->printResult();
        echo '<br><br>';
        echo '<hr>';
    }
}