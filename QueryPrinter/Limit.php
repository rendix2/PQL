<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 1. 2020
 * Time: 16:38
 */

namespace pql\QueryPrinter;

/**
 * Trait Limit
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryPrinter
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
