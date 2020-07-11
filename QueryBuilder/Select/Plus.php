<?php

namespace pql\QueryBuilder\Select;

class Plus implements IExpression
{

    /**
     * @var IExpression[] $expressions
     */
    private $expressions;

    /**
     * Plus constructor.
     *
     * @param IExpression ...$expressions
     */
    public function __construct(IExpression... $expressions)
    {
        $this->expressions = $expressions;
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        return implode('+', $this->expressions);
    }
}