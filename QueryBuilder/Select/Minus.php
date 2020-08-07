<?php

namespace pql\QueryBuilder\Select;

/**
 * Class Minus
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select
 */
class Minus implements ISelectExpression
{
    /**
     * @var ISelectExpression[]  $expressions
     */
    private $expressions;

    /**
     * Minus constructor.
     *
     * @param ISelectExpression ...$expressions
     */
    public function __construct(ISelectExpression... $expressions)
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
