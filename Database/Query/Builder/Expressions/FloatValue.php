<?php
/**
 *
 * Created by PhpStorm.
 * Filename: FloatValue.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 21:21
 */

namespace PQL\Database\Query\Builder\Expressions;

/**
 * Class FloatValue
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class FloatValue extends AbstractExpression implements INumberValue
{
    /**
     * @var float $value
     */
    private float $value;

    /**
     * @param float       $value
     * @param string|null $alias
     */
    public function __construct(float $value, ?string $alias = null)
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
     * @return float
     */
    public function evaluate() : float
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
        return (string)$this->value;
    }
}