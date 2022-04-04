<?php
/**
 *
 * Created by PhpStorm.
 * Filename: NullValue.php
 * User: Tomáš Babický
 * Date: 30.08.2021
 * Time: 14:00
 */

namespace PQL\Database\Query\Builder\Expressions;

/**
 * Class NullValue
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class NullValue extends AbstractExpression implements IValue
{
    /**
     * @return null
     */
    public function evaluate()
    {
        return null;
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null): string
    {
        return 'null';
    }
}