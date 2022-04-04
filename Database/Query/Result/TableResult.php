<?php
/**
 *
 * Created by PhpStorm.
 * Filename: TableResult.php
 * User: Tomáš Babický
 * Date: 28.08.2021
 * Time: 2:49
 */

namespace PQL\Database\Query\Result;

use PQL\Database\IPrintable;
use PQL\Database\Query\Builder\SelectBuilder;

/**
 * Class TableResult
 *
 * @package PQL\Database\Query\Result
 */
class TableResult implements IPrintable
{
    /**
     * @var SelectBuilder $query
     */
    private SelectBuilder $query;

    /**
     * TableResult constructor.
     *
     * @param SelectBuilder $query
     */
    public function __construct(SelectBuilder $query)
    {
        $this->query = $query;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null) : string
    {
        if (count($this->query->getResult())) {
            $table = '<table border="1">';
            $table .= '<thead><tr>';

            foreach ($this->query->getColumns() as $column) {
                if ($column->hasAlias()) {
                    $table .= '<th>' . $column->getAlias() . '</th>';
                } else {
                    $table .= '<th>' . $column->print() . '</th>';
                }
            }

            $table .= '</tr></thead><tbody>';

            foreach ($this->query->getResult() as $rows) {
                $table .= '<tr>';

                foreach ($rows as $row) {
                    if ($row) {
                        $table .= '<td>' . $row . '</td>';
                    } elseif ($row === null) {
                        $table .= '<td><i>NULL</i></td>';
                    }
                }
                $table .= '</tr>';
            }

            return $table . '</tbody></table>';
        } else {
            return 'No rows';
        }
    }
}
