<?php

namespace pql\QueryBuilder\Select;

class Expression implements IExpression
{
    /**
     * @var IExpression[] $expressions
     */
    private $expressions;

    /**
     * Expression constructor.
     *
     * @param IExpression ...$expressions
     */
    public function __construct(IExpression ...$expressions)
    {
        $this->expressions = $expressions;
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        return implode(',' , $this->expressions);
    }
}