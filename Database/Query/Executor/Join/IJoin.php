<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IJoin.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 12:22
 */

namespace PQL\Query\Runner\Join;

use PQL\Query\Builder\Expressions\ICondition;

interface IJoin
{
    public function leftJoin(array $leftRows, array $rightRows, ICondition $where, \stdClass $nullColumns);

    public function rightJoin(array $aRows, array $bRows, ICondition $where, array $nullColumns);

    public function innerJoin(array $leftRows, array $rightRows, ICondition $where);

}