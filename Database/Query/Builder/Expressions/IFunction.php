<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IFunction.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 16:11
 */

namespace PQL\Query\Builder\Expressions;

interface IFunction extends ISelect
{

    public function getName() : string;

    public function getArguments() : array;

}