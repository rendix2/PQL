<?php

namespace pql\QueryBuilder\From;

interface IExpression
{

    /**
     * @return string
     */
    public function evaluate();

}