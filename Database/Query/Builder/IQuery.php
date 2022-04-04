<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IQuery.php
 * User: Tomáš Babický
 * Date: 17.09.2021
 * Time: 22:43
 */

namespace PQL\Database\Query\Builder;

use PQL\Database\IPrintable;

interface IQuery extends IPrintable
{
    public function execute();

}