<?php
/**
 *
 * Created by PhpStorm.
 * Filename: WhereCondition.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 10:52
 */

namespace PQL\Database\Query\Builder\Expressions;

use Nette\NotImplementedException;
use PQL\Database\IPrintable;

/**
 * Class WhereCondition
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class WhereCondition implements ICondition, BinaryExpression
{
    /**
     * @var IExpression $left
     */
    private IExpression $left;

    /**
     * @var WhereOperator $operator
     */
    private WhereOperator $operator;

    /**
     * @var IValue $right
     */
    private IValue $right;

    /**
     * Where constructor.
     *
     * @param IExpression   $left
     * @param WhereOperator $operator
     * @param IValue       $right
     */
    public function __construct(
        IExpression   $left,
        WhereOperator $operator,
        IValue       $right
    ) {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    /**
     * Where destructor.
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return IExpression
     */
    public function getLeft() : IExpression
    {
        return $this->left;
    }

    /**
     * @return WhereOperator
     */
    public function getOperator() : WhereOperator
    {
        return $this->operator;
    }

    /**
     * @return IValue
     */
    public function getRight() : IValue
    {
        return $this->right;
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
        return sprintf(
            '%s %s %s',
            $this->left->evaluate(),
            $this->operator->evaluate(),
            $this->right->evaluate()
        );
    }
}