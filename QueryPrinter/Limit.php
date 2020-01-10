<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 1. 2020
 * Time: 16:38
 */

namespace pql\QueryPrinter;

/**
 * Class Limit
 *
 * @package pql\QueryPrinter
 * @author  rendix2 <rendix2@seznam.cz>
 */
trait Limit
{
    /**
     * @return string
     */
    private function limit()
    {
        $limit = '';

        if ($this->query->getLimit()) {
            $limit = '<br> LIMIT ' . $this->query->getLimit();
        }

        return $limit;
    }
}
