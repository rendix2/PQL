<?php

namespace pql\QueryBuilder\Select;

/**
 * Class Minus
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select
 */
class Minus implements IMathExpression
{
    /**
     * @var ISelectExpression[]  $expressions
     */
    private array $expressions;

    /**
     * Minus constructor.
     *
     * @param ISelectExpression ...$expressions
     */
    public function __construct(ISelectExpression... $expressions)
    {
        $this->expressions = $expressions;
    }

    public function evaluate()
    {
        $result = '';

        foreach ($this->expressions as $i =>$expression) {
            if ($i !== 0) {
                $result .= '-';
            }

            $result .= $expression->evaluate() ;
        }

        return $result;
    }

    public function result(): int|float
    {
        $result = $this->expressions[0]->getValue();
        $expressions = $this->expressions;

        array_shift($expressions);

        foreach ($expressions as $i =>$expression) {
            $result -= $expression->getValue();
        }

        return $result;
    }
}
