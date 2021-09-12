<?php
/**
 *
 * Created by PhpStorm.
 * Filename: ISelect.php
 * User: Tomáš Babický
 * Date: 02.09.2021
 * Time: 0:15
 */

namespace PQL\Query\Builder\Expressions;


use PQL\IPrintable;

interface ISelect extends IExpression, IPrintable
{

    public function getName() : string;

    public function hasAlias() : bool;

    public function getAlias() : ?string;


}