<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IFromExpression.php
 * User: Tomáš Babický
 * Date: 31.08.2021
 * Time: 13:54
 */

namespace PQL\Database\Query\Builder\Expressions;

use PQL\Database\Table;
use stdClass;

/**
 * Interface IFromExpression
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
interface IFromExpression extends IExpression
{
    public function getData() : array;

    public function getNullEntity() : stdClass;

    public function getTable() : Table;
}
