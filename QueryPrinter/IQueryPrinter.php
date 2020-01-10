<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 1. 2020
 * Time: 17:21
 */

namespace pql\QueryPrinter;

/**
 * Interface IQueryPrinter
 *
 * @package pql\QueryPrinter
 * @author  rendix2 <rendix2@seznam.cz>
 */
interface IQueryPrinter
{
    /**
     * @return string
     */
    public function printQuery();
}
