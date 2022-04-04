<?php
/**
 *
 * Created by PhpStorm.
 * Filename: UpdatePrinter.php
 * User: Tomáš Babický
 * Date: 21.09.2021
 * Time: 21:54
 */

namespace PQL\Database\Query\Printer;

use PQL\Database\Query\Builder\Expressions\Set;
use PQL\Database\Query\Builder\UpdateBuilder;

/**
 * Class UpdatePrinter
 *
 * @package PQL\Database\Query\Printer
 */
class UpdatePrinter
{
    /**
     * @var UpdateBuilder $query
     */
    private UpdateBuilder $query;

    /**
     * @param UpdateBuilder $query
     */
    public function __construct(UpdateBuilder $query)
    {
        $this->query = $query;
    }

    public function print() : string
    {
        $update = 'UPDATE ' . $this->query->getTable()->evaluate() . ' ';

        $update .= $this->set();
        $update .= $this->where();

        return $update;

    }

    private function set() : string
    {
        $sets = array_map(
            static function (Set $set) {
                return $set->print();
            },
            $this->query->getSetExpressions()
        );

        return 'SET ' . implode(', ', $sets) . ' ';
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
                $where .= 'AND';
            }
        }

        return $where;
    }
}
