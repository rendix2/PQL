<?php
/**
 *
 * Created by PhpStorm.
 * Filename: FloatValue.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 21:21
 */

namespace PQL\Query\Builder\Expressions;

use Nette\NotImplementedException;

class FloatValue extends AbstractExpression implements INumberValue
{
    private float $value;

    /**
     * @param float       $value
     * @param null|string $alias
     */
    public function __construct(float $value, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->value = $value;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function evaluate() : float
    {
        return $this->value;
    }

    public function print(?int $level = null) : string
    {
        return (string)$this->value;
    }
}