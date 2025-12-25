<?php

namespace pql\QueryBuilder\Select;

/**
 * Class AggregateFunction
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select
 */
abstract class AggregateFunction implements ISelectExpression
{
    /**
     * @var string
     */
    const AVERAGE = 'avg';

    /**
     * @var string
     */
    const SUM = 'sum';

    /**
     * @var string
     */
    const MIN = 'min';

    /**
     * @var string
     */
    const MAX = 'max';

    /**
     * @var string
     */
    const MEDIAN = 'median';

    /**
     * @var string
     */
    const COUNT = 'count';

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $column
     */
    private $column;

    /**
     * AggregateFunction constructor.
     *
     * @param string $name
     * @param string $column
     */
    public function __construct($name, $column)
    {
        $this->name = $name;
        $this->column = $column;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    public function evaluate()
    {
        return $this->name . '(' . $this->column . ')';
    }
}
