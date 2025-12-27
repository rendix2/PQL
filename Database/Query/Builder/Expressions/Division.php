<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Division.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 21:25
 */

namespace PQL\Database\Query\Builder\Expressions;

use Exception;

/**
 * Class Division
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class Division extends AbstractExpression implements IMathOperator
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
     * @param string|null     $alias
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
     * @throws Exception
     */
    public function evaluate() : float
    {
        $right = $this->right->evaluate();

        if ($right === 0) {
            throw new Exception('Division by zero.');
        }

        return $this->left->evaluate() / $right;
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null) : string
    {
        return sprintf('%s / %s', $this->left->print(), $this->right->print());
    }
}
