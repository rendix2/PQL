<?php

namespace pql\QueryBuilder\Select;

/**
 * Class Plus
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select
 */
class Plus implements IMathExpression
{
    /**
     * @var ISelectExpression[] $expressions
     */
    private $expressions;

    /**
     * Plus constructor.
     *
     * @param ISelectExpression ...$expressions
     */
    public function __construct(ISelectExpression... $expressions)
    {
        $this->expressions = $expressions;
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        $result = '';

        foreach ($this->expressions as $i =>$expression) {
            if ($i !== 0) {
                $result .= '+';
            }

            $result .= $expression->evaluate() ;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function result()
    {
        $result = 0;

        foreach ($this->expressions as $i => $expression) {
            $result += $expression->result();
        }

        return $result;
    }
}
