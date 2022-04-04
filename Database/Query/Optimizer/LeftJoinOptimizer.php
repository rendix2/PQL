<?php
/**
 *
 * Created by PhpStorm.
 * Filename: LeftJoinOptimizer.php
 * User: Tomáš Babický
 * Date: 23.09.2021
 * Time: 10:02
 */

namespace PQL\Database\Query\Optimizer;

use PQL\Database\Query\Builder\Expressions\ICondition;
use PQL\Database\Query\Executor\Join\IJoin;
use stdClass;

/**
 * Class LeftJoinOptimizer
 *
 * @package PQL\Database\Query\Optimizer
 */
class LeftJoinOptimizer
{
    /**
     * @param IJoin      $join
     * @param array      $leftRows
     * @param array      $rightRows
     * @param ICondition $condition
     * @param stdClass   $nullColumns
     *
     * @return array
     */
    public function leftJoin(
        IJoin      $join,
        array      $leftRows,
        array      $rightRows,
        ICondition $condition,
        stdClass   $nullColumns
    ) : array {
        return $join->leftJoin($leftRows, $rightRows, $condition, $nullColumns);
    }
}
