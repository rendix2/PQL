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

    private static array $unaryOperators = [
        'IS NULL',
        'IS NOT NULL',
    ];

    private static array $binaryOperators = [
        '=',
        '>',
        '<',
        '<=',
        '>=',
        '!=',
        '<>',
        'IN',
        'NOT IN',
        'BETWEEN',
        'BETWEEN_INCLUSIVE',
    ];

    /**
     * @var string|mixed $operator
     */
    private string $operator;

    /**
     * @var bool $isUnary
     */
    private bool $isUnary;

    /**
     * @var bool $isBinary
     */
    private bool $isBinary;

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

        $this->isUnary = in_array($operator, static::$unaryOperators);
        $this->isBinary = in_array($operator, static::$binaryOperators);
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

    /**
     * @return bool
     */
    public function isUnary() : bool
    {
        return $this->isUnary;
    }

    /**
     * @return bool
     */
    public function isBinary() : bool
    {
        return $this->isBinary;
    }
}
