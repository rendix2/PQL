<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:58
 */

namespace pql\QueryBuilder;

use pql\QueryExecutor\Explain as ExplainExecutor;
use pql\QueryResult\IResult;
use pql\QueryResult\ListResult;
use pql\QueryResult\TableResult;

/**
 * Class Explain
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryBuilder
 */
class Explain extends Select
{
    /**
     * @var IResult $result
     */
    private $result;

    public function run()
    {
        if ($this->result instanceof TableResult) {
            return $this->result;
        }

        set_time_limit(0);

        $startTime = microtime(true);

        $explain = new ExplainExecutor($this);

        $rows = $explain->run();

        $endTime = microtime(true);
        $executeTime  = $endTime - $startTime;

        return $this->result = new ListResult($rows);
    }
}
