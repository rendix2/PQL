<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IJoin.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 12:22
 */

namespace PQL\Database\Query\Executor\Join;

use PQL\Database\Query\Builder\Expressions\ICondition;
use stdClass;

interface IJoin
{
    public function leftJoin(array $leftRows, array $rightRows, ICondition $where, stdClass $nullColumns);

    public function rightJoin(array $leftRows, array $rightRows, ICondition $where, array $nullColumns);

    public function innerJoin(array $leftRows, array $rightRows, ICondition $where);

}