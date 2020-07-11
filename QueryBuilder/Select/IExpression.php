<?php

namespace pql\QueryBuilder\Select;

interface IExpression
{
    /**
     * @return string
     */
    public function evaluate();

}