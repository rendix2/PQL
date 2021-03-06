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
    /**
     * @var string $column
     */
    private $column;

    /**
     * @var ISelectExpression $expression
     */
    private $expression;

    /**
     * @var Alias $alias
     */
    private $alias;

    /**
     * @var bool $hasAlias
     */
    private $hasAlias;

    /**
     * @var bool $hasTableAlias
     */
    private $hasTableAlias;

    /**
     * SelectedColumn constructor.
     *
     * @param string      $column
     * @param ISelectExpression $expression
     * @param Alias|null  $alias
     */
    public function __construct($column, ISelectExpression $expression, Alias $alias = null)
    {
        $this->column        = $column;
        $this->expression    = $expression;
        $this->alias         = $alias;
        $this->hasAlias      = $alias !== null;
        $this->hasTableAlias = Alias::hasAlias($column);
    }

    /**
     * SelectedColumn destructor.
     */
    public function __destruct()
    {
        $this->column = null;
        $this->alias = null;
        $this->hasAlias = null;
        $this->hasTableAlias = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->hasAlias ? $this->column . ' AS ' . $this->alias->getTo() : $this->column;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return ISelectExpression
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @return Alias
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return bool
     */
    public function hasAlias()
    {
        return $this->hasAlias;
    }

    /**
     * @return bool
     */
    public function hasTableAlias()
    {
        return $this->hasTableAlias;
    }
}
