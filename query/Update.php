<?php
namespace query;

use Query;

/**
 * Class Update
 *
 * @author  rendix2
 * @package query
 */
class Update
{
    /**
     * @var Query $query
     */
    private $query;

    /**
     * Update constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Update destructor.
     */
    public function __destruct()
    {
        $this->query = null;
    }

    /**
     *
     */
    public function run()
    {
        $this->where();
        $this->limit();
    }

    /**
     *
     */
    private function where()
    {
    }

    /**
     *
     */
    private function limit()
    {
    }
}

