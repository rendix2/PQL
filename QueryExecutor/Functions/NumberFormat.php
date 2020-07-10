<?php

namespace pql\QueryExecutor\Functions;

/**
 * Class NumberFormat
 *
 * @package pql\QueryExecutor\Functions
 */
class NumberFormat implements IFunction
{
    /**
     * @var string
     */
    const FUNCTION_NAME = 'number_format';

    /**
     * @inheritDoc
     */
    public function run($column, ...$params)
    {
        $params = array_merge([$column], $params[0]);

        return number_format(...$params);
    }
}