<?php

namespace pql;

/**
 * Class GroupByColumn
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class GroupByColumn
{
    private string $column;

    public function __construct(string $column)
    {
        $this->column = $column;
    }

    public function __toString(): string
    {
        return $this->column;
    }

    public function getColumn(): string
    {
        return $this->column;
    }
}
