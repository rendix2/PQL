<?php
/**
 *
 * Created by PhpStorm.
 * Filename: HavingCondition.php
 * User: Tomáš Babický
 * Date: 10.09.2021
 * Time: 2:14
 */

namespace PQL\Query\Builder\Expressions;

use Nette\NotImplementedException;

class HavingCondition implements ICondition
{
    /**
     * @var AggregateFunctionExpression $left
     */
    private AggregateFunctionExpression $left;

    /**
     * @var Operator $operator
     */
    private Operator $operator;

    /**
     * @var IExpression $right
     */
    private IExpression $right;

    /**
     * @param AggregateFunctionExpression $left
     * @param Operator                    $operator
     * @param IExpression                 $right
     */
    public function __construct(AggregateFunctionExpression $left, Operator $operator, IExpression $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    /**
     * @return AggregateFunctionExpression
     */
    public function getLeft(): AggregateFunctionExpression
    {
        return $this->left;
    }

    /**
     * @return Operator
     */
    public function getOperator(): Operator
    {
        return $this->operator;
    }

    /**
     * @return IExpression
     */
    public function getRight(): IExpression
    {
        return $this->right;
    }

    public function evaluate() : string
    {
        throw new NotImplementedException();
    }
}