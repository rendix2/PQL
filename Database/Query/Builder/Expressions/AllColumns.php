<?php

namespace PQL\Database\Query\Builder\Expressions;

/**
 * class AllColumns
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class AllColumns extends AbstractExpression implements ISelect
{
    public function __construct()
    {
        parent::__construct();
    }

    public function evaluate()
    {
        throw new \Exception();
    }

    public function print(?int $level = null) : string
    {
        return '*';
    }

    public function hasAlias() : bool
    {
        return false;
    }

    public function getAlias() : ?string
    {
        return null;
    }
}
