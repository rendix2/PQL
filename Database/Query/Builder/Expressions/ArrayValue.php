<?php
/**
 *
 * Created by PhpStorm.
 * Filename: ArrayValue.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 22:34
 */

namespace PQL\Database\Query\Builder\Expressions;

/**
 * Class ArrayValue
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
abstract class ArrayValue extends AbstractExpression implements IValue
{
    /**
     * @var array $values
     */
    private array $values;

    /**
     * @param array       $values
     * @param string|null $alias
     */
    public function __construct(array $values, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->values = $values;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }

        parent::__destruct();
    }

    /**
     * @return array
     */
    public function evaluate() : array
    {
        return $this->values;
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null) : string
    {
        return sprintf('(%s)', implode(', ', $this->values));
    }
}