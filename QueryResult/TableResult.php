<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 1. 2. 2019
 * Time: 15:15
 */

namespace pql\QueryResult;

use Generator;
use pql\ITable;
use pql\QueryExecutor\IQueryExecutor;
use pql\QueryRow\TableRow;
use pql\SelectedColumn;

final class TableResult implements ITable, IResult
{
    private float $executeTime;
    
    private float $formattedTime;

    /**
     * @var int|null $affectedRows
     */
    private ?int $affectedRows;

    /**
     * @var TableRow[] $rows
     */
    private array $rows;

    private int $rowsCount;

    /**
     * @var SelectedColumn[] $columns
     */
    private array $columns;

    private int $columnsCount;

    private IQueryExecutor $query;

    public function __construct(
        array $columns,
        array $rows,
        float $executeTime,
        IQueryExecutor $query,
        int $affectedRows = 0
    )
    {
        $this->rows          = $rows;
        $this->rowsCount     = count($rows);
        $this->columns       = $columns;
        $this->columnsCount  = count($columns);
        $this->executeTime   = $executeTime;
        $this->formattedTime = (float)number_format($executeTime, 5);
        $this->query         = $query;
        $this->affectedRows  = $affectedRows;
    }

    public function __toString(): string
    {
        if ($this->rowsCount) {
            $table  = '<table border="1">';
            $table .= '<thead><tr>';

            foreach ($this->columns as $column) {
                $table .= sprintf('<td>%s</td>', $column->getColumn());
            }

            $table.= '</tr></thead><tbody>';

            foreach ($this->rows as $row) {
                $table .= '<tr>';

                foreach ($this->columns as $columnList) {
                    $value = $row->get()->{$columnList->getColumn()};

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
     * @return SelectedColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getRowsCount(): int
    {
        return $this->rowsCount;
    }

    public function getColumnsCount(): int
    {
        return $this->columnsCount;
    }

    public function getRows(bool $returnObject = false): Generator
    {
        yield $this->rows;
    }

    public function getQuery() : IQueryExecutor
    {
        return $this->query;
    }
}
