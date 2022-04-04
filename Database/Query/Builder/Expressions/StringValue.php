<?php
/**
 *
 * Created by PhpStorm.
 * Filename: SingleValue.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 11:02
 */

namespace PQL\Database\Query\Builder\Expressions;

/**
 * Class StringValue
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class StringValue extends AbstractExpression implements IValue
{
    /**
     * @var string $value
     */
    private string $value;

    /**
     * StringValue constructor.
     *
     * @param string      $value
     * @param string|null $alias
     */
    public function __construct(string $value, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->value = $value;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }

        parent::__destruct();
    }

    /**
     * @return string
     */
    public function evaluate() : string
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
        return sprintf('"%s"', $this->value);
    }
}
