<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Division.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 21:25
 */

namespace PQL\Query\Builder\Expressions;

use Exception;

class Division extends AbstractExpression implements IMathOperator
{

    private IMathExpression $left;

    private IMathExpression $right;

    /**
     * @param IMathExpression $left
     * @param IMathExpression $right
     * @param null|string     $alias
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
        $right = $this->right->evaluate();

        if ($right === 0) {
            throw new Exception('Division by zero.');
        }

        return $this->left->evaluate() / $this->right->evaluate();
    }

    public function print(): string
    {
        return sprintf('%s / %s', $this->left->print(), $this->right->print());
    }
}