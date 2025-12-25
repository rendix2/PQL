<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 11. 12. 2019
 * Time: 16:52
 */

namespace pql;

/**
 * Class OrderByColumn
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class OrderByColumn
{
    private string $column;


    private bool $asc;

    public function __construct(string $column, bool $asc = true)
    {
        $this->column = $column;
        $this->asc    = $asc;
    }

    public function __toString(): string
    {
        return $this->column . ' ' . $this->getSortingWord();
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getAsc(): bool
    {
        return $this->asc;
    }

    public function getSortingConst(): int
    {
        return $this->asc === true ? SORT_ASC : SORT_DESC;
    }

    public function getSortingWord(): string
    {
        return $this->asc === true ? 'ASC' : 'DESC';
    }
}
