<?php


namespace pql\QueryBuilder\Select;


interface IMathExpression extends ISelectExpression
{
    /**
     * @return int|float
     */
    public function result();
}