<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IExpression.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 10:49
 */

namespace PQL\Query\Builder\Expressions;


interface IExpression
{
    public function evaluate();
}
