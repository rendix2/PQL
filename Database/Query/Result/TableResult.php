<?php
/**
 *
 * Created by PhpStorm.
 * Filename: TableResult.php
 * User: Tomáš Babický
 * Date: 28.08.2021
 * Time: 2:49
 */

namespace PQL\Query\Result;


use PQL\IPrintable;
use PQL\Query\Builder\Select;

class TableResult implements IPrintable
{

    private array $data;

    private Select $query;

    /**
     * TableResult constructor.
     *
     * @param array $data
     */
    public function __construct(Select $query, array $data)
    {
        $this->query = $query;
        $this->data = $data;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function print(?int $level = null) : string
    {
        if (count($this->data)) {
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

            foreach ($this->data as $rows) {
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