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
    public function run(string $column, mixed ...$params): mixed;
}