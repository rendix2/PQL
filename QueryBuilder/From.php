<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 12:11
 */

namespace pql\QueryBuilder;

use Exception;
use pql\Alias;
use pql\QueryBuilder\From\IFromExpression;
use pql\Table;

/**
 * Trait From
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder
 */
trait From
{
    private ?Table $table;

    public function from(IFromExpression $table, ?string $alias = null): SelectQuery
    {
        $this->table = $this->checkTable($table);

        if ($alias) {
            $this->tableAlias = new Alias($this->table, $alias);
            $this->hasTableAlias = true;
        }

        return $this;
    }
}
