<?php
/**
 *
 * Created by PhpStorm.
 * Filename: FunctionsExecutor.php
 * User: Tomáš Babický
 * Date: 10.09.2021
 * Time: 0:56
 */

namespace PQL\Database\Query\Executor;

use Exception;
use Nette\NotImplementedException;
use PQL\Database\Query\Builder\Expressions\Column;
use PQL\Database\Query\Builder\Expressions\FunctionExpression;
use PQL\Database\Query\Builder\Expressions\StringValue;
use PQL\Database\Query\Builder\SelectBuilder;

/**
 * Class FunctionsExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class FunctionsExecutor implements IExecutor
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @param SelectBuilder $query
     */
    public function __construct(SelectBuilder $query)
    {
        $this->query = $query;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    public function run(array $rows) : array
    {
        foreach ($this->query->getFunctions() as $function) {
            $countArguments = $function->getCountArguments();

            if ($countArguments === 1) {
                $rows = $this->singleArgument($function, $rows);
            } elseif ($countArguments > 1) {
                throw new NotImplementedException();
            }
        }

        return $rows;
    }

    /**
     * @throws Exception
     */
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
            throw new Exception('Unknown input.');
        }

        return $rows;
    }
}
