<?php
/**
 *
 * Created by PhpStorm.
 * Filename: ISelect.php
 * User: Tomáš Babický
 * Date: 02.09.2021
 * Time: 0:15
 */

namespace PQL\Database\Query\Builder\Expressions;

use PQL\Database\IPrintable;

/**
 * Interface ISelect
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
interface ISelect extends IExpression
{
    public function hasAlias() : bool;

    public function getAlias() : ?string;
}
