<?php
/**
 *
 * Created by PhpStorm.
 * Filename: FunctipmExpression.php
 * User: Tomáš Babický
 * Date: 02.09.2021
 * Time: 0:08
 */

namespace PQL\Database\Query\Builder\Expressions;

use Exception;

/**
 * Class AggregateFunctionExpression
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class AggregateFunctionExpression extends AbstractFunction
{
    /**
     * @var string[] $availableFunctions
     */
    private static array $availableFunctions = [
        'min',
        'max',
        'avg',
        'sum',
        'count',
    ];

    /**
     * @param string      $name
     * @param array       $arguments
     * @param string|null $alias
     *
     * @throws Exception
     */
    public function __construct(string $name, array $arguments, ?string $alias = null)
    {
        parent::__construct($name, $arguments, $alias);

        $aggregateFunctionExist = in_array($this->getLowerName(), static::$availableFunctions, true);

        if (!$aggregateFunctionExist) {
            $message = sprintf('Aggregate function "%s" does not exit.', $name);

            throw new Exception($message);
        }
    }
}
