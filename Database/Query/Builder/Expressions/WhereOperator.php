<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Operator.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 10:53
 */

namespace PQL\Database\Query\Builder\Expressions;

use Exception;

/**
 * Class WhereOperator
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class WhereOperator implements IOperator
{
    /**
     * @var array|string[] $operators
     */
    private static array $operators = [
        '=',
        '>',
        '<',
        '<=',
        '>=',
        '!=',
        '<>',
        'IN',
        'NOT IN',
        'IS NULL',
        'IS NOT NULL',
        'BETWEEN',
        'BETWEEN_INCLUSIVE',
    ];

    /**
     * @var string|mixed $operator
     */
    private string $operator;

    /**
     * @throws Exception
     */
    public function __construct($operator)
    {
        if (!in_array($operator, static::$operators, true)) {
            $message = sprintf(
                'Unknown operator %s. Allowed operators are: %s',
                $operator,
                implode(', ', static::$operators)
            );

            throw new Exception($message);
        }

        $this->operator = $operator;
    }

    /**
     *
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return string
     */
    public function evaluate() : string
    {
        return $this->operator;
    }
}
