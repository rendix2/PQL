<?php
/**
 *
 * Created by PhpStorm.
 * Filename: RightJoinExecutor.php
 * User: Tomáš Babický
 * Date: 23.09.2021
 * Time: 10:19
 */

namespace PQL\Database\Query\Executor\Select;

use Nette\NotImplementedException;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Executor\IExecutor;

/**
 * Class RightJoinExecutor
 *
 * @package PQL\Database\Query\Executor\Select
 */
class RightJoinExecutor implements IExecutor
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * @param array $rows
     *
     * @return array
     */
    public function run(array $rows) : array
    {
        throw new NotImplementedException();
    }
}
