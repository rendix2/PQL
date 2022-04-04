<?php
/**
 *
 * Created by PhpStorm.
 * Filename: DeletePrinter.php
 * User: Tomáš Babický
 * Date: 21.09.2021
 * Time: 21:54
 */

namespace PQL\Database\Query\Printer;

use PQL\Database\Query\Builder\DeleteBuilder;

/**
 * Class DeletePrinter
 *
 * @package PQL\Database\Query\Printer
 */
class DeletePrinter
{
    /**
     * @var DeleteBuilder $query
     */
    private DeleteBuilder $query;

    /**
     * @param DeleteBuilder $query
     */
    public function __construct(DeleteBuilder $query)
    {
        $this->query = $query;
    }

    public function print() : string
    {
        $update = 'DELETE FROM ' . $this->query->getTable()->evaluate() . ' ';

        $update .= $this->where();

        return $update;
    }

    private function where() : string
    {
        if (!count($this->query->getWhereConditions())) {
            return '';
        }

        $where = 'WHERE ';

        foreach ($this->query->getWhereConditions() as $i => $whereCondition) {
            $where .= $whereCondition->print();

            if ($i !== 0) {
                $where .= 'AND ';
            }
        }

        return $where;
    }
}
