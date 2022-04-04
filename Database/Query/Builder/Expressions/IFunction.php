<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IFunction.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 16:11
 */

namespace PQL\Database\Query\Builder\Expressions;

/**
 * Interface IFunction
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
interface IFunction extends ISelect
{
    public function getLowerName() : string;

    public function getUpperName() : string;

    public function getArguments() : array;

    public function getCountArguments() : int;
}