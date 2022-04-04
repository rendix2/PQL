<?php
/**
 *
 * Created by PhpStorm.
 * Filename: HavingCondition.php
 * User: Tomáš Babický
 * Date: 10.09.2021
 * Time: 2:14
 */

namespace PQL\Database\Query\Builder\Expressions;

use Nette\NotImplementedException;

/**
 * Class HavingCondition
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class HavingCondition implements ICondition, BinaryExpression
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
     * @var IExpression $right
     */
    private IExpression $right;

    /**
     * @param AggregateFunctionExpression $left
     * @param WhereOperator               $operator
     * @param IExpression                 $right
     */
    public function __construct(IExpression $left, WhereOperator $operator, IExpression $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
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
     * @return IExpression
     */
    public function getRight() : IExpression
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
        throw new NotImplementedException();
    }
}
