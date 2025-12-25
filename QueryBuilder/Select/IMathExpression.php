<?php


namespace pql\QueryBuilder\Select;


interface IMathExpression extends ISelectExpression
{
    public function result(): int|float;
}