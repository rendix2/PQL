<?php

namespace pql\QueryExecutor\Functions;

/**
 * Interface IFunction
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecutor\Functions
 */
interface IFunction
{
    /**
     * @param string $column
     * @param mixed ...$params
     * @return mixed
     */
    public function run($column, ...$params);
}