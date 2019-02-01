<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 1. 2. 2019
 * Time: 15:15
 */

/**
 * Class Result
 *
 * @author Tomáš Babický tomas.babicky@websta.de
 */
class Result
{
    private $time;

    private $affectedRows;

    private $rowsCount;

    private $rows;

    private $columns;

    /**
     * Result constructor.
     *
     * @param array $rows
     */
    public function __construct(array $columns, array $rows)
    {
        $this->rows      = $rows;
        $this->rowsCount = count($rows);
        $this->coolumns  = $columns;
    }

    /**
     * Result destructor.
     */
    public function __destruct()
    {
        $this->rows         = null;
        $this->time         = null;
        $this->rowsCount    = null;
        $this->affectedRows = null;
        $this->columns      = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (count($this->rows)) {
            $table  = '<table>';
            $table .= '<thead><tr>';

            foreach ($this->coolumns as $column) {
                $table .= sprintf('<td>%s</td>', $column);
            }

            $table.= '</tr></thead><tbody>';

            foreach ($this->rows as $row) {
                $table .= '<tr>';

                foreach ($row as $column) {
                    $table .= sprintf('<td>%s</td>', $column);
                }

                $table .= '</tr>';
            }

            $table .= '</tbody></table>';

            return $table;
        }
    }
}
