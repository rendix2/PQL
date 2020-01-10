<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 1. 2020
 * Time: 16:22
 */

namespace pql\QueryPrinter;

use pql\Query;

/**
 * Class Delete
 *
 * @package pql\QueryPrinter
 * @author  rendix2 <rendix2@seznam.cz>
 * @internal
 */
class Delete implements IQueryPrinter
{
    use Where;
    use Limit;
    use Offset;

    /**
     * @var Query $query
     */
    private $query;

    /**
     * Delete constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Delete destructor.
     */
    public function __destruct()
    {
        $this->query = null;
    }

    /**
     * @inheritDoc
     */
    public function printQuery()
    {
        $delete = 'DELETE FROM ' . $this->query->getTable()->getName();

        $where  = $this->where();
        $limit  = $this->limit();
        $offset = $this->offset();

        return $delete . $where . $limit . $offset . '<br><br>';
    }
}
