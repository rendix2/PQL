<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10. 1. 2020
 * Time: 16:39
 */

namespace pql\QueryPrinter;

/**
 * Class Offset
 *
 * @package pql\QueryPrinter
 * @author  rendix2 <rendix2@seznam.cz>
 */
trait Offset
{
    /**
     * @return string
     */
    private function offset()
    {
        $offset = '';

        if ($this->query->getOffset() !== 0) {
            $offset = '<br> OFFSET ' . $this->query->getOffset();
        }

        return $offset;
    }
}
