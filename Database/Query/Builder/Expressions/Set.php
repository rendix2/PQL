<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Set.php
 * User: Tomáš Babický
 * Date: 21.09.2021
 * Time: 22:15
 */

namespace PQL\Database\Query\Builder\Expressions;

use Nette\NotImplementedException;

/**
 * Class Set
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class Set implements IExpression
{
    /**
     * @var Column $column
     */
    private Column $column;

    /**
     * @var IValue $value
     */
    private IValue $value;

    /**
     * Set constructor
     *
     * @param Column $column
     * @param IValue $value
     */
    public function __construct(Column $column, IValue $value)
    {
        $this->column = $column;
        $this->value = $value;
    }

    /**
     *
     */
    public function evaluate()
    {
        throw new NotImplementedException();
    }

    /**
     * @return Column
     */
    public function getColumn() : Column
    {
        return $this->column;
    }

    /**
     * @return IValue
     */
    public function getValue() : IValue
    {
        return $this->value;
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null) : string
    {
        return $this->column->print() . ' = ' . $this->value->print();
    }
}
