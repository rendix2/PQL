<?php
/**
 *
 * Created by PhpStorm.
 * Filename: CreteTestData.php
 * User: Tomáš Babický
 * Date: 15.09.2021
 * Time: 1:28
 */

namespace PQL;

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use PQL\Tests\SelectTestQueryFactory;
use ReflectionClass;

class TestDataCreator
{
    public function __construct()
    {
    }

    public function run() : void
    {
        $selectTest = new SelectTestQueryFactory();

        $classReflection = new ReflectionClass($selectTest);
        $methods = $classReflection->getMethods();

        foreach ($methods as $method) {
            $isTestMethod = str_starts_with($method->getName(), 'test');

            if ($isTestMethod) {
                $query = $selectTest->{$method->getName()}();
                $rows = $query->execute();

                $this->writeFunctionWithData($rows, ucfirst($method->getName()));
            }
        }
    }

    private function writeFunctionWithData(array $rows, string $className): void
    {
        $nameSpace = new PhpNamespace(SelectTestQueryFactory::$nameSpace);

        $class = $nameSpace->addClass($className);
        $class->addImplement('\PQL\Tests\InputData\ITestData');

        $method = $class->addMethod('getData');
        $method->setReturnType('array');

        $arrayString = '[' . "\n";

        foreach ($rows as $row) {
            $arrayString .= "\t" . '[';

            foreach ($row as $column => $value) {
                if (is_numeric($value)) {
                    if (is_float($value)) {
                        $arrayString .= '"' . $column . '" => ' . number_format($value, 1) . ', ';
                    } else {
                        $arrayString .= '"' . $column . '" => ' . $value . ', ';
                    }
                } elseif ($value === null) {
                    $arrayString .= '"' . $column . '" => null, ';
                } else {
                    $arrayString .= '"' . $column . '" => "' . $value . '", ';
                }
            }

            $arrayString .= '],' . "\n";
        }

        $arrayString .= '];';

        $method->setBody('return ' . $arrayString);

        $phpFile = new PhpFile();
        $phpFile->addNamespace($nameSpace);

        file_put_contents('tests/expectedData/' . $className . '.php', $phpFile);
    }
}