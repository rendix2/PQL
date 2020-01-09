<?php

namespace pql;

/**
 * Class GroupByColumn
 *
 * @author rendix2 <rendix2@seznam.cz>
 */
class GroupByColumn
{
    /**
     * @var string $column
     */
    private $column;

    /**
     * GroupByColumn constructor.
     *
     * @param string $column
     */
    public function __construct($column)
    {
        $this->column = $column;
    }

    /**
     * GroupByColumn destructor.
     */
    public function __destruct()
    {
        $this->column = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }
}
