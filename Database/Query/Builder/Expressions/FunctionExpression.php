<?php
/**
 *
 * Created by PhpStorm.
 * Filename: FunctionExpression.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 16:11
 */

namespace PQL\Query\Builder\Expressions;

use Exception;

class FunctionExpression extends AbstractExpression implements IFunction
{
    /**
     * @var IExpression[] $arguments
     */
    private array $arguments;

    /**
     * @var string $name
     */
    private string $name;

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
     * @param null|string $alias
     *
     * @throws Exception
     */
    public function __construct(string $name, array $arguments, ?string $alias = null)
    {
        parent::__construct($alias);

        if (isset(static::$phpFunctions[$name])) {
            $phpFunctionName = static::$phpFunctions[$name];
        } else {
            $phpFunctionName = $name;
        }

        if (!function_exists($phpFunctionName)) {
            $message = sprintf('Function "%s" does not exists', $phpFunctionName);

            throw new \Exception($message);
        }

        $this->name = mb_strtolower($name);
        $this->phpName = $phpFunctionName;

        foreach ($arguments as $argument) {
            if (!($argument instanceof IExpression)) {
                $message = 'Argument is not Expression';

                throw new Exception($message);
            }
        }

        $this->arguments = $arguments;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPhpName(): string
    {
        return $this->phpName;
    }

    /**
     * @return IExpression[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function evaluate() : string
    {
        $arguments = array_map(
            static function (IExpression $expression) {
                return $expression->evaluate();
            }, $this->arguments
        );

        $arguments = implode(', ', $arguments);

        return sprintf('%s(%s)', mb_strtoupper($this->name), $arguments);
    }

    public function print(?int $level = null): string
    {
        $arguments = array_map(
            static function (IExpression $expression) {
                return $expression->print();
            }, $this->arguments
        );

        $arguments = implode(', ', $arguments);

        return sprintf('%s(%s)', mb_strtoupper($this->name), $arguments);
    }
}