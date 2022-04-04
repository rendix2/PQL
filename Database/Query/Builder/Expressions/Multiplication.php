<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Multiplication.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 21:20
 */

namespace PQL\Database\Query\Builder\Expressions;

/**
 * Class Multiplication
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class Multiplication extends AbstractExpression implements IMathBinaryOperator
{
    /**
     * @var IMathExpression $left
     */
    private IMathExpression $left;

    /**
     * @var IMathExpression $right
     */
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

        parent::__destruct();
    }

    /**
     * @return IMathExpression
     */
    public function getLeft() : IMathExpression
    {
        return $this->left;
    }

    /**
     * @return IMathExpression
     */
    public function getRight() : IMathExpression
    {
        return $this->right;
    }

    /**
     * @return float
     */
    public function evaluate() : float
    {
        return $this->left->evaluate() * $this->right->evaluate();
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null) : string
    {
        return sprintf('(%s + %s)', $this->left->print(), $this->right->print());
    }
}
