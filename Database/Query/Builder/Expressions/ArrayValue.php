<?php
/**
 *
 * Created by PhpStorm.
 * Filename: ArrayValue.php
 * User: Tomáš Babický
 * Date: 13.09.2021
 * Time: 22:34
 */

namespace PQL\Query\Builder\Expressions;

use Nette\NotImplementedException;

class ArrayValue implements IValue
{
    private array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function evaluate() : array
    {
        return $this->values;
    }

    public function print(?int $level = null): string
    {
        return sprintf('(%s)', implode(', ', $this->values));
    }
}