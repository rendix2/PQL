<?php

namespace pql\QueryBuilder\Select;

class Minus implements IExpression
{
    /**
     * @var IExpression[]  $expressions
     */
    private $expressions;

    /**
     * Minus constructor.
     *
     * @param IExpression ...$expressions
     */
    public function __construct(IExpression... $expressions)
    {
        $this->expressions = $expressions;
    }

    /**
     * @return string
     */
    public function evaluate()
    {
        return implode('-', $this->expressions);
    }
}