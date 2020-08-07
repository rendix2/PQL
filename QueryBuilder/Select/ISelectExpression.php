<?php

namespace pql\QueryBuilder\Select;

/**
 * Interface ISelectExpression
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder\Select
 */
interface ISelectExpression
{
    /**
     * @return string
     */
    public function evaluate();

}
