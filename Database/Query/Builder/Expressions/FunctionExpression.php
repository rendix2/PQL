<?php
/**
 *
 * Created by PhpStorm.
 * Filename: FunctionExpression.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 16:11
 */

namespace PQL\Database\Query\Builder\Expressions;

use Exception;

/**
 * Class FunctionExpression
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class FunctionExpression extends AbstractFunction
{
    /**
     * @var string[] $phpFunctions
     */
    private static array $phpFunctions = [
        'strtoupper' => 'mb_strtoupper',
        'strtolower' => 'mb_strtolower',
    ];

    /**
     * @var string $phpName
     */
    private string $phpName;

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

        if (isset(static::$phpFunctions[$name])) {
            $phpFunctionName = static::$phpFunctions[$name];
        } else {
            $phpFunctionName = $name;
        }

        if (!function_exists($phpFunctionName)) {
            $message = sprintf('Function "%s" does not exists', $phpFunctionName);

            throw new Exception($message);
        }

        $this->phpName = $phpFunctionName;
    }

    /**
     *
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }

        parent::__destruct();
    }

    /**
     * @return string
     */
    public function getPhpName(): string
    {
        return $this->phpName;
    }
}
