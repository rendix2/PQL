<?php

namespace pql;

/**
 * Class ListResult
 *
 * @package pql
 */
class ListResult
{
    /**
     * @var ExplainRow[] $rows
     */
    private $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function __destruct()
    {
        $this->rows = null;
    }

    public function __toString()
    {
    }

    public function printQuery(Query $query)
    {
        $rows = '<ul>';

        if ($query->getTable() instanceof Table) {
            $rows .= '<li>' . $query->getTable()->getName() . '<li>';
        } elseif ($query->getTable() instanceof Query) {
            $rows .= $this->printQuery($query->getTable());
        }

        $rows .= '</ul>';

        return $rows;
    }
}