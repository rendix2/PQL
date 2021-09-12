<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Minus.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 18:05
 */

namespace PQL\Query\Builder\Expressions;

use Nette\NotImplementedException;

class Minus extends AbstractExpression implements IMathOperator
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

    public function evaluate() : float
    {
        return $this->left->evaluate() - $this->right->evaluate();
    }

    public function print(?int $level = null): string
    {
        return sprintf('(%s - %s)', $this->left->print(), $this->right->print());
    }

    public function getName(): string
    {
        throw new NotImplementedException();
    }
}