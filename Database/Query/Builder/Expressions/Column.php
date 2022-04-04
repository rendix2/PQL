<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Column.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 10:44
 */

namespace PQL\Database\Query\Builder\Expressions;

use PQL\Database\Table;

/**
 * Class Column
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class Column extends AbstractExpression implements ISelect
{
    /**
     * @var string $name
     */
    private string $name;

    /**
     * @var TableExpression $tableExpression
     */
    private TableExpression $tableExpression;

    /**
     * @var Table $table
     */
    private Table $table;

    /**
     * Column constructor.
     *
     * @param string          $name
     * @param TableExpression $tableExpression
     * @param string|null     $alias
     */
    public function __construct(string $name, TableExpression $tableExpression, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->name = $name;
        $this->tableExpression = $tableExpression;
        $this->table = $this->tableExpression->getTable();
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }

        parent::__destruct();
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return TableExpression
     */
    public function getTableExpression() : TableExpression
    {
        return $this->tableExpression;
    }

    /**
     * @return Table
     */
    public function getTable() : Table
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getTableColumnName() : string
    {
        return $this->tableExpression->getTable()->getName() . '.' . $this->name;
    }

    /**
     * @return string
     */
    public function evaluate() : string
    {
        return $this->tableExpression->evaluate() . '.' . $this->name;
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null) : string
    {
        return $this->tableExpression->evaluate() . '.' . $this->name;
    }
}