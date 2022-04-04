<?php
/**
 *
 * Created by PhpStorm.
 * Filename: JoinCondition.php
 * User: Tomáš Babický
 * Date: 30.08.2021
 * Time: 17:10
 */

namespace PQL\Database\Query\Builder\Expressions;

use PQL\Database\Query\Builder\JoinOperator;

/**
 * Class JoinCondition
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class JoinCondition implements ICondition, BinaryExpression
{
    /**
     * @var Column $left
     */
    private Column $left;

    /**
     * @var JoinOperator $operator
     */
    private JoinOperator $operator;

    /**
     * @var Column $right
     */
    private Column $right;

    /**
     * Where constructor.
     *
     * @param Column       $left
     * @param JoinOperator $operator
     * @param Column       $right
     */
    public function __construct(
        Column       $left,
        JoinOperator $operator,
        Column       $right
    ) {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    /**
     * JoinCondition destructor.
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return Column
     */
    public function getLeft() : Column
    {
        return $this->left;
    }

    /**
     * @return JoinOperator
     */
    public function getOperator() : JoinOperator
    {
        return $this->operator;
    }

    /**
     * @return Column
     */
    public function getRight() : Column
    {
        return $this->right;
    }

    /**
     * @return string
     */
    public function evaluate() : string
    {
        return sprintf('%s %s %s',
            $this->left->evaluate(),
            $this->operator->evaluate(),
            $this->right->evaluate()
        );
    }

    public function print(?int $level = null) : string
    {
        return $this->evaluate();
    }
}
