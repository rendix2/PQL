<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Table.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 10:49
 */

namespace PQL\Database\Query\Builder\Expressions;

use PQL\Database\Database;
use PQL\Database\IPrintable;
use PQL\Database\Table;
use stdClass;

/**
 * Class TableExpression
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class TableExpression extends AbstractExpression implements IFromExpression
{
    /**
     * @var Database $database
     */
    private Database $database;

    /**
     * @var string $table
     */
    private string $table;

    /**
     * @var array|null $data
     */
    private ?array $data;

    /**
     * Table constructor.
     *
     * @param Database    $database
     * @param string      $tableExpression
     * @param string|null $alias
     */
    public function __construct(Database $database, string $tableExpression, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->database = $database;
        $this->table = $tableExpression;

        $this->data = null;
    }

    /**
     *
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }

        parent::__destruct();
    }

    /**
     * @return Table
     */
    public function getTable() : Table
    {
        return $this->database->getTable($this->table);
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        if (!$this->data) {
            $this->data = $this->getTable()->getAllData();
        }

        return $this->data;
    }

    /**
     * @return stdClass
     */
    public function getNullEntity(): stdClass
    {
        $entity = new stdClass();

        foreach ($this->getTable()->getColumns() as $column) {
            $entity->{$column->tableName} = null;
        }

        return $entity;
    }

    /**
     * @return string
     */
    public function evaluate() : string
    {
        return $this->table;
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null) : string
    {
        return $this->evaluate();
    }
}