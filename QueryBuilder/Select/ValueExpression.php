<?php

namespace pql\QueryBuilder\Select;

/**
 * Class Value
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select
 */
class ValueExpression implements ISelectExpression
{
    /**
     * @var string|int $value
     */
    private $value;

    /**
     * Value constructor.
     * @param string|int $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        return $this->value;
    }

    /**
     * @return string|int
     */
    public function getValue()
    {
        return $this->value;
    }
}
