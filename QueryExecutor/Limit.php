<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 13:38
 */

namespace pql\QueryExecutor;

use pql\QueryRow\TableRow;

/**
 * Class Limit
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
trait Limit
{
    /**
     * @var int $limit
     */
    private $limit;

    /**
     * @return array|TableRow[]
     */
    protected function limit()
    {
        if (!$this->query->getLimit() && $this->query->getOffset() === 0) {
            return $this->result;
        }

        $rowsCount = count($this->result);

        $limit = $this->query->getLimit() > $rowsCount ? $rowsCount : $this->query->getLimit();

        return $this->result = array_slice($this->result, $this->query->getOffset(), $limit, true);
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }
}
