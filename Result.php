<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 1. 2. 2019
 * Time: 15:15
 */

use query\BaseQuery;

/**
 * Class Result
 *
 * @author rendix2
 */
final class Result implements ITable
{
    /**
     * @var float $time
     */
    private $time;
    
    /**
     * @var float $timeFormatted
     */
    private $timeFormatted;

    /**
     * @var int|null $affectedRows
     */
    private $affectedRows;

    /**
     * @var int $rowsCount
     */
    private $rowsCount;

    /**
     * @var Row[] $rows
     */
    private $rows;

    /**
     * @var array $columns
     */
    private $columns;
    /**
     * @var BaseQuery $query
     */
    private $query;

    /**
     * Result constructor.
     *
     * @param array     $columns
     * @param array     $rows
     * @param float     $time
     * @param BaseQuery $query
     * @param int       $affectedRows
     */
    public function __construct(array $columns, array $rows, $time, BaseQuery $query, $affectedRows = 0)
    {
        $this->rows          = $rows;
        $this->rowsCount     = count($rows);
        $this->columns       = $columns;
        $this->time          = $time;
        $this->timeFormatted = (float)number_format($time, 5);
        $this->query         = $query;
        $this->affectedRows  = $affectedRows;
    }

    /**
     * Result destructor.
     */
    public function __destruct()
    {
        $this->rows          = null;
        $this->time          = null;
        $this->timeFormatted = null;
        $this->rowsCount     = null;
        $this->affectedRows  = null;
        $this->columns       = null;
        $this->query         = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (count($this->rows)) {
            $table  = '<table border="1">';
            $table .= '<thead><tr>';

            foreach ($this->columns as $column) {
                $table .= sprintf('<td>%s</td>', $column);
            }

            $table.= '</tr></thead><tbody>';

            foreach ($this->rows as $row) {
                $table .= '<tr>';

                foreach ($this->columns as $columnList) {
                    $value = $row->get()->{$columnList};

                    if ($value === null) {
                        $value = '<i>NULL</i>';
                    }

                    $table .= sprintf('<td>%s</td>', mb_convert_encoding($value, 'utf8'));
                }

                $table .= '</tr>';
            }

            $table .= '</tbody></table>';

            return $table;
        }

        return 'No result';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param bool $object
     *
     * @return array|Row[]
     */
    public function getRows($object = false)
    {
        return $this->rows;
    }

    /**
     * @return BaseQuery
     */
    public function getQuery()
    {
        return $this->query;
    }
}
