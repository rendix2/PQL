<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Multiplication.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 21:20
 */

namespace PQL\Query\Builder\Expressions;

class Multiplication extends AbstractExpression implements IMathOperator
{
    private IMathExpression $left;

    private IMathExpression $right;

    /**
     * @param IMathExpression $left
     * @param IMathExpression $right
     * @param string|null     $alias
     */
    public function __construct(IMathExpression $left, IMathExpression $right, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->left = $left;
        $this->right = $right;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function evaluate() : float
    {
        return $this->left->evaluate() * $this->right->evaluate();
    }

    public function print(): string
    {
        return sprintf('(%s + %s)', $this->left->print(), $this->right->print());
    }
}