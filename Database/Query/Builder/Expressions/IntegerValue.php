<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IntegerValue.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 11:03
 */

namespace PQL\Query\Builder\Expressions;


use Nette\NotImplementedException;

class IntegerValue extends AbstractExpression implements INumberValue
{

    private int $value;

    /**
     * IntegerValue constructor.
     *
     * @param int         $value
     * @param null|string $alias
     */
    public function __construct(int $value, ?string $alias = null)
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

    public function evaluate() : int
    {
        return $this->value;
    }

    public function getName(): string
    {
        throw new NotImplementedException();
    }

    public function print(?int $level = null): string
    {
        return $this->value;
    }
}