<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 1. 2020
 * Time: 16:38
 */

namespace pql\QueryPrinter;

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
                if ($whereCondition->getValue() instanceof Query) {
                    $value = '(<br><br>' . (string)$whereCondition->getValue() . '<br><br>)';
                } elseif (is_array($whereCondition->getValue())) {
                    $value =  '(' . implode(self::IN_SEPARATOR, $whereCondition->getValue()) . ')';
                } else {
                    $value = $whereCondition->getValue();
                }

                if ($whereCondition->getColumn() instanceof Query) {
                    $column = '(<br><br>' . (string)$whereCondition->getColumn() . '<br><br>)';
                } elseif (is_array($whereCondition->getColumn())) {
                    $column = '(' . implode(self::IN_SEPARATOR, $whereCondition->getColumn()) . ')';
                } else {
                    $column = $whereCondition->getColumn();
                }

                $where .= ' ' . $column . ' ' . mb_strtoupper($whereCondition->getOperator()) . ' ' . $value;

                if ($whereCount !== $i) {
                    $where .= ' <br> &nbsp;&nbsp;&nbsp;&nbsp;AND';
                }
            }
        }

        return $where;
    }
}
