<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 1. 2020
 * Time: 16:38
 */

namespace pql\QueryPrinter;

use pql\Query;

/**
 * Trait Where
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryPrinter
 */
trait Where
{
    /**
     * @return string
     */
    private function where()
    {
        $whereCount = count($this->query->getWhereConditions());
        $where = '';

        if ($whereCount) {
            $where = ' <br>WHERE ';

            --$whereCount;

            foreach ($this->query->getWhereConditions() as $i => $whereCondition) {
                $where . (string) $whereCondition;

                if ($whereCount !== $i) {
                    $where .= ' <br> &nbsp;&nbsp;&nbsp;&nbsp;AND';
                }
            }
        }

        return $where;
    }
}
