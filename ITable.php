<?php

namespace pql;

use Generator;
use pql\QueryRow\TableRow;

/**
 * Interface ITable
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
interface ITable
{
    /**
     * @return TableColumn[]
     */
    public function getColumns(): array;

    public function getRows(bool $returnObject = false): Generator;
}
