<?php

namespace pql\QueryBuilder\Select;

/**
 * Class Expression
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select
 */
class Expression implements ISelectExpression
{
    /**
     * @var ISelectExpression[] $expressions
     */
    private $expressions;

    /**
     * Expression constructor.
     *
     * @param ISelectExpression ...$expressions
     */
    public function __construct(ISelectExpression ...$expressions)
    {
        $this->expressions = $expressions;
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        $result = '';

        foreach ($this->expressions as $expression) {
            $result .= $expression->evaluate();
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function result()
    {
        $result = 0;

        foreach ($this->expressions as $expression) {
            $result += $expression->result();
        }

        return $result;
    }
}
