<?php

namespace pql\QueryResult;

use pql\ExplainRow;

/**
 * Class ListResult
 *
 * @author rendix2 <rendix2@seznam.cz>
 * @package pql\QueryResult
 */
class ListResult implements IResult
{
    /**
     * @var ExplainRow[] $rows
     */
    private $rows;

    /**
     * ListResult constructor.
     *
     * @param array $rows
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    /**
     * ListResult destructor.
     */
    public function __destruct()
    {
        $this->rows = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->printQuery($this->rows);
    }

    /**
     * @param $rows
     * @return string
     */
    public function printQuery($rows)
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
