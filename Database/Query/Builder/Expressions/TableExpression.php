<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Table.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 10:49
 */

namespace PQL\Query\Builder\Expressions;


use PQL\Database;
use PQL\IPrintable;
use PQL\Table;
use stdClass;

class TableExpression extends AbstractExpression implements IFromExpression, IPrintable
{
    private Database $database;

    private string $table;

    /**
     * Table constructor.
     *
     * @param Database    $database
     * @param string      $table
     * @param null|string $alias
     */
    public function __construct(Database $database, string $table, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->database = $database;
        $this->table = $table;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->database->getTable($this->table);
    }

    public function getData() : array
    {
        return $this->getTable()->getAllData();
    }

    public function getNullEntity(): stdClass
    {
        $entity = new stdClass();

        foreach ($this->getTable()->getColumns() as $column) {
            $entity->{$column->tableName} = null;
        }

        return $entity;
    }

    public function evaluate() : string
    {
        return $this->table;
    }

    public function print(?int $level = null): string
    {
        return $this->evaluate();
    }
}