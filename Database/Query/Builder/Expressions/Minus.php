<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Minus.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 18:05
 */

namespace PQL\Database\Query\Builder\Expressions;

/**
 * Class Minus
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class Minus extends AbstractExpression implements IMathBinaryOperator
{
    /**
     * @var IExpression $left
     */
    private IExpression $left;

    /**
     * @var IExpression $right
     */
    private IExpression $right;

    /**
     * @param IExpression $left
     * @param IExpression $right
     * @param string|null $alias
     */
    public function __construct(IExpression $left, IExpression $right, ?string $alias = null)
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
     * @return IExpression
     */
    public function getLeft() : IExpression
    {
        return $this->left;
    }

    /**
     * @return IExpression
     */
    public function getRight() : IExpression
    {
        return $this->right;
    }

    /**
     * @return float
     */
    public function evaluate() : float
    {
        return $this->left->evaluate() - $this->right->evaluate();
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null): string
    {
        return sprintf('(%s - %s)', $this->left->print(), $this->right->print());
    }
}