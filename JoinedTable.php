<?php

namespace pql;

use pql\QueryBuilder\Query;

/**
 * Class JoinedTable
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class JoinedTable
{
    /**
     * @var Table|Query $table
     */
    private $table;

    /**
     * @var Condition[] $onConditions
     */
    private $onConditions;

    /**
     * @var Alias|null $alias
     */
    private $alias;

    /**
     * @var bool $hasAlias
     */
    private $hasAlias;

    public function __construct(Table|Query $table, array $onConditions, string $alias = null)
    {
        $this->table = $table;
        $this->onConditions = $onConditions;

        if ($alias) {
            $this->alias = new Alias($table, $alias);
            $this->hasAlias = true;
        } else {
            $this->alias = null;
            $this->hasAlias = false;
        }
    }

    /**
     * @return Table|Query
     */
    public function getTable(): Table|Query
    {
        return $this->table;
    }

    /**
     * @return Condition[]
     */
    public function getOnConditions()
    {
        return $this->onConditions;
    }

    /**
     * @return Alias|null
     */
    public function getAlias()
    {
        return $this->alias;
    }

    public function hasAlias(): bool
    {
        return $this->hasAlias;
    }
}
