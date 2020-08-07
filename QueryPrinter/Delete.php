<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 1. 2020
 * Time: 16:22
 */

namespace pql\QueryPrinter;

use pql\QueryBuilder\DeleteQuery as DeleteBuilder;

/**
 * Class Delete
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryPrinter
 * @internal
 */
class Delete implements IQueryPrinter
{
    use Where;
    use Limit;
    use Offset;

    /**
     * @var DeleteBuilder $query
     */
    private $query;

    /**
     * Delete constructor.
     *
     * @param DeleteBuilder $query
     */
    public function __construct(DeleteBuilder $query)
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
