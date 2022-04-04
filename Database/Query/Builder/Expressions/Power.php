<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Power.php
 * User: Tomáš Babický
 * Date: 08.01.2022
 * Time: 13:58
 */

namespace PQL\Database\Query\Builder\Expressions;

/**
 * Class Power
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class Power extends AbstractExpression implements IMathBinaryOperator
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
     * Power constructor.
     *
     * @param IExpression $left
     * @param IExpression $right
     * @param string|null     $alias
     */
    public function __construct(IExpression $left, IExpression $right, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->left = $left;
        $this->right = $right;
    }

    /**
     * Power destructor.
     *
     */
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
    public function evaluate()
    {
        return $this->left->evaluate() ** $this->right->evaluate();
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null) : string
    {
        return sprintf('(%s^%s)', $this->left->print(), $this->right->print());
    }
}
