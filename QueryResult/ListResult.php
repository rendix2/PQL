<?php

namespace pql\QueryResult;

use pql\QueryRow\ExplainRow;

/**
 * Class ListResult
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryResult
 */
class ListResult implements IResult
{
    /**
     * @var ExplainRow[] $rows
     */
    private array $rows;

    /**
     * ListResult constructor.
     *
     * @param array $rows
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function __toString(): string
    {
        return $this->printQuery($this->rows);
    }

    public function printQuery(array $rows): string
    {
        $list = '<ol>';

        foreach ($rows as $row) {
            if (is_array($row->getSub())) {
                $list .= '<li>' . $row . $this->printQuery($row->getSub()) . '</li>';
            } else {
                $list .= '<li>' . $row . '</li>';
            }
        }

        $list .= '</ol>';

        return $list;
    }
}
