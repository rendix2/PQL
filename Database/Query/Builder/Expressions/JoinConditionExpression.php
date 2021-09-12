<?php
/**
 *
 * Created by PhpStorm.
 * Filename: JoinCondition.php
 * User: Tomáš Babický
 * Date: 30.08.2021
 * Time: 17:10
 */

namespace PQL\Query\Builder\Expressions;


class JoinConditionExpression implements ICondition
{
    private IExpression $left;

    private Operator $operator;

    private ?IExpression $right;

    /**
     * Where constructor.
     *
     * @param IExpression $left
     * @param Operator    $operator
     * @param ?IExpression $right
     */
    public function __construct(
        IExpression $left,
        Operator $operator,
        ?IExpression $right = null
    ) {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return IExpression
     */
    public function getLeft(): IExpression
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
     * @return ?IExpression
     */
    public function getRight(): ?IExpression
    {
        return $this->right;
    }

    public function evaluate() : string
    {
        return sprintf('%s %s %s',
            $this->left->evaluate(),
            $this->operator->evaluate(),
            $this->right->evaluate()
        );
    }
}