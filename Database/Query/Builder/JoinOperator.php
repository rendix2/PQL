<?php
/**
 *
 * Created by PhpStorm.
 * Filename: JoinOperator.php
 * User: Tomáš Babický
 * Date: 21.10.2021
 * Time: 15:10
 */

namespace PQL\Database\Query\Builder;

use Exception;
use PQL\Database\Query\Builder\Expressions\IOperator;

/**
 * Class JoinOperator
 *
 * @package PQL\Database\Query\Builder
 */
class JoinOperator implements IOperator
{
    private static array $operators = [
        '=',
    ];

    private string $operator;

    /**
     * @throws Exception
     */
    public function __construct($operator)
    {
        if (!in_array($operator, static::$operators, true)) {
            $message = sprintf('Unknown operator "%s". Allowed operators are: "%s"', $operator, implode(', ', static::$operators));

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