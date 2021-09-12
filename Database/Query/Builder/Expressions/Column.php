<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Column.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 10:44
 */

namespace PQL\Query\Builder\Expressions;


class Column extends AbstractExpression implements ISelect
{
    private string $name;

    private TableExpression $table;

    /**
     * Column constructor.
     *
     * @param string          $name
     * @param TableExpression $table
     * @param null|string     $alias
     */
    public function __construct(string $name, TableExpression $table, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->name = $name;
        $this->table = $table;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return TableExpression
     */
    public function getTable(): TableExpression
    {
        return $this->table;
    }

    public function getTableColumnName() : string
    {
        return $this->table->getTable()->getName() . '.' . $this->name;
    }

    public function evaluate() : string
    {
        return $this->table->evaluate() . '.' . $this->name;
    }

    public function print(?int $level = null) : string
    {
        return $this->table->evaluate() . '.' . $this->name;
    }
}