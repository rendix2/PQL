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

    /**
     * JoinedTable constructor.
     * @param Table|Query $table
     * @param Condition[] $onConditions
     * @param Alias|null  $alias
     */
    public function __construct($table, array $onConditions, $alias = null)
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
     * JoinedTable destructor.
     */
    public function __destruct()
    {
        $this->table = null;
        $this->alias = null;
        $this->hasAlias = null;

        foreach ($this->onConditions as &$onCondition) {
            $onCondition = null;
        }

        unset($onCondition);

        $this->onConditions = null;
    }

    /**
     * @return Table|Query
     */
    public function getTable()
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

    /**
     * @return bool
     */
    public function hasAlias()
    {
        return $this->hasAlias;
    }
}
