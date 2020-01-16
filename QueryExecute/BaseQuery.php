<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 8. 3. 2019
 * Time: 15:37
 */

namespace pql\QueryExecute;

use pql\Query;
use pql\TableRow;

/**
 * Class Query
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
abstract class BaseQuery
{
    /**
     * @var Query $query
     */
    protected $query;

    /**
     * @var array $result
     */
    protected $result;

    /**
     * Query constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query  = $query;
        $this->result = [];
    }

    /**
     * Query destructor.
     */
    public function __destruct()
    {
        $this->query  = null;
        $this->result = null;
    }

    /**
     * @return mixed
     */
    abstract public function run();

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
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }
}
