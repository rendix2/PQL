<?php
/**
 *
 * Created by PhpStorm.
 * Filename: InnerJoinOptimizer.php
 * User: Tomáš Babický
 * Date: 23.09.2021
 * Time: 10:04
 */

namespace PQL\Database\Query\Optimizer;

use PQL\Database\Query\Builder\Expressions\ICondition;
use PQL\Database\Query\Executor\Join\IJoin;

/**
 * Class InnerJoinOptimizer
 *
 * @package PQL\Database\Query\Optimizer
 */
class InnerJoinOptimizer
{
    /**
     * @param IJoin      $join
     * @param array      $leftRows
     * @param array      $rightRows
     * @param ICondition $condition
     *
     * @return array
     */
    public function innerJoin(IJoin $join, array $leftRows, array $rightRows, ICondition $condition) : array
    {
        $leftRowsCount = count($leftRows);
        $rightRowsCount = count($rightRows);

        if ($leftRowsCount > $rightRowsCount) {
            return $join->innerJoin($leftRows, $rightRows, $condition);
        } else {
            return $join->innerJoin($rightRows, $leftRows, $condition);
        }
    }
}
