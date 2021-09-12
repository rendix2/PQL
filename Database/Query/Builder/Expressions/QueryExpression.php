<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Query.php
 * User: Tomáš Babický
 * Date: 31.08.2021
 * Time: 13:54
 */

namespace PQL\Query\Builder\Expressions;


use Nette\NotImplementedException;
use PQL\IPrintable;
use PQL\Query\Builder\Select;
use PQL\Query\Printer\Select as SelectPrinter;
use PQL\Query\Runner\SelectExecutor;
use stdClass;

class QueryExpression extends AbstractExpression implements IFromExpression, IPrintable
{
    private Select $select;

    private SelectExecutor $selectExecutor;

    public function __construct(Select $select, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->select = $select;
        $this->selectExecutor = new SelectExecutor($this->select);
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function getQuery() : Select
    {
        return $this->select;
    }

    public function getData() : array
    {
        return $this->selectExecutor->run();
    }

    public function getNullEntity(): stdClass
    {
        $entity = new stdClass();

        foreach ($this->select->getColumns() as $column) {
            $entity->{$column->evaluate()} = null;
        }

        return $entity;
    }

    public function evaluate() : string
    {
        throw new NotImplementedException();
    }

    public function print(?int $level = null): string
    {
        $printer = new SelectPrinter($this->select, $level);

        return $printer->print();
    }
}