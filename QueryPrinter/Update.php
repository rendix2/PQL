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
 * Class Update
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryPrinter
 * @internal
 */
class Update implements IQueryPrinter
{
    use Where;
    use Limit;
    use Offset;

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
     * @inheritDoc
     */
    public function printQuery()
    {
        $update = 'UPDATE ' . $this->query->getTable()->getName();

        $set = ' SET ';

        $i = 0;
        $count = count($this->query->getUpdateData());

        foreach ($this->query->getUpdateData() as $column => $value) {
            $i++;

            $set .= $column . ' = ' . $value;

            if ($count !== $i) {
                $set .= ', ';
            }
        }

        $where  = $this->where();
        $limit  = $this->limit();
        $offset = $this->offset();

        return $update . $set . $where . $limit . $offset . '<br><br>';
    }
}
