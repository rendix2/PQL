<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IntegerValue.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 11:03
 */

namespace PQL\Database\Query\Builder\Expressions;

/**
 * Class IntegerValue
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class IntegerValue extends AbstractExpression implements INumberValue
{
    /**
     * @var int $value
     */
    private int $value;

    /**
     * IntegerValue constructor.
     *
     * @param int         $value
     * @param string|null $alias
     */
    public function __construct(int $value, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->value = $value;
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
     * @return int
     */
    public function evaluate() : int
    {
        return $this->value;
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null) : string
    {
        return $this->value;
    }
}
