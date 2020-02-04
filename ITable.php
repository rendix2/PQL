<?php

namespace pql;

use pql\QueryRow\TableRow;

/**
 * Interface ITable
 *
 * @author rendix2 <rendix2@seznam.cz>
 * @package pql
 */
interface ITable
{
    /**
     * @return TableColumn[]
     */
    public function getColumns();

    /**
     * @param bool $object
     *
     * @return TableRow[]|array
     */
    public function getRows($object = false);
}
