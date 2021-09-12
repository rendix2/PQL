<?php
/**
 *
 * Created by PhpStorm.
 * Filename: AbstractExpression.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 23:52
 */

namespace PQL\Query\Builder\Expressions;

abstract class AbstractExpression implements IExpression
{

    private ?string $alias;

    private bool $hasAlias;

    public function __construct(?string $alias = null)
    {
        $this->alias = $alias;
        $this->hasAlias = $alias !== null;
    }

    /**
     * @return null|string
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @return bool
     */
    public function hasAlias(): bool
    {
        return $this->hasAlias;
    }
}