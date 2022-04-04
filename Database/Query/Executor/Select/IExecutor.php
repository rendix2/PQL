<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IExecutor.php
 * User: Tomáš Babický
 * Date: 05.09.2021
 * Time: 1:32
 */

namespace PQL\Database\Query\Executor;


interface IExecutor
{

    public function run(array $rows) : array;

}