<?php
/**
 *
 * Created by PhpStorm.
 * Filename: JoinScheduler.php
 * User: Tomáš Babický
 * Date: 23.09.2021
 * Time: 10:04
 */

namespace PQL\Database\Query\Scheduler;

use PQL\Database\Query\Executor\Join\IJoin;

/**
 * Class JoinScheduler
 *
 * @package PQL\Database\Query\Scheduler
 */
class JoinScheduler
{
    /**
     * @var string $dataSource
     */
    private string $dataSource;

    /**
     * @var IJoin $join
     */
    private IJoin $join;

    /**
     * @param string $dataSource
     * @param IJoin  $join
     */
    public function __construct(string $dataSource, IJoin $join)
    {
        $this->dataSource = $dataSource;
        $this->join = $join;
    }

    /**
     * @return string
     */
    public function getDataSource() : string
    {
        return $this->dataSource;
    }

    /**
     * @return IJoin
     */
    public function getJoin() : IJoin
    {
        return $this->join;
    }
}