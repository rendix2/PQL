<?php
/**
 *
 * Created by PhpStorm.
 * Filename: FunctipmExpression.php
 * User: Tomáš Babický
 * Date: 02.09.2021
 * Time: 0:08
 */

namespace PQL\Query\Builder\Expressions;


use Exception;

class AggregateFunctionExpression extends AbstractExpression implements IFunction
{
    /**
     * @var IExpression[] $arguments
     */
    private array $arguments;

    /**
     * @var string $name
     */
    private string $name;

    private static array $availableFunctions = [
        'min',
        'max',
        'avg',
        'sum',
        'count',
    ];

    public function __construct(string $name, array $arguments, ?string $alias = null)
    {
        parent::__construct($alias);

        $aggregateFunctionExist = in_array(mb_strtolower($name), static::$availableFunctions, true);

        if (!$aggregateFunctionExist) {
            $message = sprintf('Aggregate function "%s" does not exit.', $name);

            throw new Exception($message);
        }

        foreach ($arguments as $argument) {
            if (!($argument instanceof IExpression)) {
                $message = 'Argument is not Expression';

                throw new Exception($message);
            }
        }

        $this->name = mb_strtoupper($name);
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
     * @return IExpression[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function evaluate() : string
    {
        $function = $this->name . '(';

        $count = count($this->arguments);

        foreach ($this->arguments as $i => $argument) {
            $function .= $argument->evaluate();

            if ($i !== $count - 1) {
                $function .= ', ';
            }
        }

        $function .= ')';

        return $function;
    }

    public function print(?int $level = null) : string
    {
        return $this->evaluate();
    }
}