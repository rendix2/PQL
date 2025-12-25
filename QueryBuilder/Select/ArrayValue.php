<?php

namespace pql\QueryBuilder\Select;

/**
 * Class ArrayValue
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select
 */
class ArrayValue implements ISelectExpression
{

    private array $values;

    /**
     * ArrayValue constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function evaluate()
    {
        return $this->values;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }
}
