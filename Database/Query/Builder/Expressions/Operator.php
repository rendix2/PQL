<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Operator.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 10:53
 */

namespace PQL\Query\Builder\Expressions;


use Exception;

class Operator implements IExpression
{

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
    ];

    private string $operator;

    /**
     * @throws Exception
     */
    public function __construct($operator)
    {
        if (!in_array($operator, static::$operators, true)) {
            $message = sprintf('Unknown operator %s.', $operator);

            throw new Exception($message);
        }

        $this->operator = $operator;
    }

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

    public function evaluate()
    {
        return $this->operator;
    }


}