<?php
/**
 *
 * Created by PhpStorm.
 * Filename: WhereCondition.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 10:52
 */

namespace PQL\Query\Builder\Expressions;


use Nette\NotImplementedException;

class WhereCondition implements ICondition
{

    private IExpression $left;

    private Operator $operator;

    private ?IValue $right;

    /**
     * Where constructor.
     *
     * @param IExpression $left
     * @param Operator    $operator
     * @param ?IValue $right
     */
    public function __construct(
        IExpression $left,
        Operator $operator,
        ?IValue $right = null
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
     * @return ?IValue
     */
    public function getRight(): ?IValue
    {
        return $this->right;
    }

    public function evaluate() : string
    {
        throw new NotImplementedException();
    }
}