<?php
/**
 *
 * Created by PhpStorm.
 * Filename: AbstractExpression.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 23:52
 */

namespace PQL\Database\Query\Builder\Expressions;

/**
 * Class AbstractExpression
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
abstract class AbstractExpression implements IExpression
{
    /**
     * @var string|null $alias
     */
    private ?string $alias;

    /**
     * @var bool $hasAlias
     */
    private bool $hasAlias;

    /**
     * @param string|null $alias
     */
    public function __construct(?string $alias = null)
    {
        $this->alias = $alias;
        $this->hasAlias = $alias !== null;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return string|null
     */
    public function getAlias() : ?string
    {
        return $this->alias;
    }

    /**
     * @return bool
     */
    public function hasAlias() : bool
    {
        return $this->hasAlias;
    }
}
