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

    /**
     * @var array $values
     */
    private $values;

    /**
     * ArrayValue constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @inheritDoc
     */
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
