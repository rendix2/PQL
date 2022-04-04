<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IExpression.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 10:49
 */

namespace PQL\Database\Query\Builder\Expressions;

use PQL\Database\IPrintable;

/**
 * Interface IExpression
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
interface IExpression extends IPrintable
{
    public function evaluate();
}
