<?php

namespace pql\QueryBuilder\From;

interface IFromExpression
{

    /**
     * @return string
     */
    public function evaluate();

}