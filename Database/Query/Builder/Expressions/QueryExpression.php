<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Query.php
 * User: Tomáš Babický
 * Date: 31.08.2021
 * Time: 13:54
 */

namespace PQL\Database\Query\Builder\Expressions;


use InvalidArgumentException;
use Nette\NotImplementedException;
use PQL\Database\IPrintable;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Printer\SelectPrinter;
use PQL\Database\Query\Executor\SelectExecutor;
use PQL\Database\Table;
use stdClass;

/**
 * Class QueryExpression
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class QueryExpression extends AbstractExpression implements IFromExpression
{
    /**
     * @var SelectBuilder $select
     */
    private SelectBuilder $select;

    /**
     * @var SelectExecutor $selectExecutor
     */
    private SelectExecutor $selectExecutor;

    /**
     * @var array|null $data
     */
    private ?array $data;

    /**
     * QueryExpression constructor
     *
     * @param SelectBuilder $select
     * @param string|null   $alias
     */
    public function __construct(SelectBuilder $select, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->select = $select;
        $this->selectExecutor = new SelectExecutor($this->select);

        $this->data = null;
    }

    /**
     * QueryExpression destructor.
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }

        parent::__destruct();
    }

    /**
     * @return SelectBuilder
     */
    public function getQuery() : SelectBuilder
    {
        return $this->select;
    }

    public function getTable() : Table
    {
        throw new InvalidArgumentException();
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        if (!$this->data) {
            $this->data = $this->selectExecutor->run();
        }

        return $this->data;
    }

    /**
     * @return stdClass
     */
    public function getNullEntity() : stdClass
    {
        $entity = new stdClass();

        foreach ($this->select->getColumns() as $column) {
            $entity->{$column->evaluate()} = null;
        }

        return $entity;
    }

    /**
     * @return string
     */
    public function evaluate() : string
    {
        throw new NotImplementedException();
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null) : string
    {
        $printer = new SelectPrinter($this->select, $level);

        return $printer->print();
    }
}
