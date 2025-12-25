<?php

namespace pql;

use pql\QueryBuilder\Select\ISelectExpression;

/**
 * Class SelectedColumn
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class SelectedColumn
{
    private string $column;

    private ISelectExpression $expression;

    private ?Alias $alias;

    private bool $hasAlias;

    private bool $hasTableAlias;

    public function __construct(string $column, ISelectExpression $expression, ?Alias $alias = null)
    {
        $this->column        = $column;
        $this->expression    = $expression;
        $this->alias         = $alias;
        $this->hasAlias      = $alias !== null;
        $this->hasTableAlias = Alias::hasAlias($column);
    }

    public function __toString(): string
    {
        return $this->hasAlias ? $this->column . ' AS ' . $this->alias->getTo() : $this->column;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getExpression(): ISelectExpression
    {
        return $this->expression;
    }

    public function getAlias(): Alias
    {
        return $this->alias;
    }

    public function hasAlias(): bool
    {
        return $this->hasAlias;
    }

    public function hasTableAlias(): bool
    {
        return $this->hasTableAlias;
    }
}
