<?php

namespace pql\QueryBuilder\Select;

/**
 * Class Column
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select
 */
class Column implements ISelectExpression
{
    /**
     * @var string $column
     */
    private $column;

    /**
     * Column constructor.
     *
     * @param string $column
     */
    public function __construct($column)
    {
        $this->column = $column;
    }

    /**
     * @inheritDoc
     */
    public function evaluate()
    {
        return $this->column;
    }
}
