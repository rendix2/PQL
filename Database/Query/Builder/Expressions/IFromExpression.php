<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IFromExpression.php
 * User: Tomáš Babický
 * Date: 31.08.2021
 * Time: 13:54
 */

namespace PQL\Query\Builder\Expressions;


use stdClass;

interface IFromExpression extends IExpression
{

    public function getData() : array;

    public function getNullEntity() : stdClass;

}