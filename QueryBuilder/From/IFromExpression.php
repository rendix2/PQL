<?php

namespace pql\QueryBuilder\From;

interface IFromExpression
{
    public function evaluate(): string;

}