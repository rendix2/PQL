<?php
/**
 *
 * Created by PhpStorm.
 * Filename: AbstractFunction.php
 * User: Tomáš Babický
 * Date: 16.09.2021
 * Time: 2:03
 */

namespace PQL\Query\Builder\Expressions;

class AbstractFunction extends AbstractExpression implements IFunction
{

    public function evaluate()
    {
        // TODO: Implement evaluate() method.
    }

    public function getName() : string
    {
        // TODO: Implement getName() method.
    }

    public function getArguments() : array
    {
        // TODO: Implement getArguments() method.
    }

    public function print(?int $level = null) : string
    {
        // TODO: Implement print() method.
    }
}