<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IMathExpression.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 18:00
 */

namespace PQL\Query\Builder\Expressions;

use PQL\IPrintable;

interface IMathExpression extends IValue, IPrintable, ISelect
{
}