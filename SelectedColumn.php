<?php


class SelectedColumn
{
    /**
     * @var string $column
     */
    private $column;

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
     * @param string     $column
     * @param Alias|null $alias
     */
    public function __construct($column, Alias $alias = null)
    {
        $this->column   = $column;
        $this->alias    = $alias;
        $this->hasAlias = $alias !== null;
        $this->hasTableAlias = Alias::hasAlias($column);
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
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