<?php

namespace pql\QueryExecutor\Functions;

class NumberFormat implements IFunction
{
    public const string FUNCTION_NAME = 'number_format';

    public function run(string $column, mixed ...$params): mixed
    {
        $params = array_merge([$column], $params[0]);

        return number_format(...$params);
    }
}
