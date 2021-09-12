<?php
/**
 *
 * Created by PhpStorm.
 * Filename: FunctionsExecutor.php
 * User: Tomáš Babický
 * Date: 10.09.2021
 * Time: 0:56
 */

namespace PQL\Query\Runner;

use Exception;
use Nette\NotImplementedException;
use PQL\Query\Builder\Expressions\Column;
use PQL\Query\Builder\Expressions\FunctionExpression;
use PQL\Query\Builder\Expressions\StringValue;
use PQL\Query\Builder\Select;

class FunctionsExecutor
{
    /**
     * @var Select $query
     */
    private Select $query;

    /**
     * @param Select $query
     */
    public function __construct(Select $query)
    {
        $this->query = $query;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function run(array $rows) : array
    {
        foreach ($this->query->getFunctions() as $function) {
            $arguments = $function->getArguments();
            $countArguments = count($arguments);

            if (count($arguments) === 1) {
                $rows = $this->singleArgument($function, $rows);
            } elseif ($countArguments > 1) {
                throw new NotImplementedException();
            }
        }

        return $rows;
    }

    private function singleArgument(FunctionExpression $function, array $rows) : array
    {
        $phpFunctionName = $function->getPhpName();
        $argument = $function->getArguments()[0];

        if ($argument instanceof Column) {
            foreach ($rows as $row) {
                $row->{$function->evaluate()} = $phpFunctionName($row->{$argument->evaluate()});
            }
        } elseif ($argument instanceof StringValue) {
            foreach ($rows as $row) {
                $row->{$function->evaluate()} = $phpFunctionName($argument->evaluate());
            }
        } else {
            throw new Exception('Unknown type of input');
        }

        return $rows;
    }
}