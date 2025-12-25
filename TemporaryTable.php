<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 5. 12. 2019
 * Time: 16:02
 */

namespace pql;

use Generator;

/**
 * Class TemporaryTable
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class TemporaryTable implements ITable
{
    private array $rows;

    private array $columns;

    public function __construct(array $rows)
    {
        $this->rows = $rows;

        if (isset($this->rows[0])) {
            $this->columns = array_keys($this->rows[0]);
        } else {
            $this->columns = array_keys($this->rows);
        }
    }

    public function addRow(array $row): TemporaryTable
    {
        $this->rows[]  = $row;
        $this->columns = array_keys($this->rows);

        return $this;
    }

    public function deleteRow(int $id): TemporaryTable
    {
        unset($this->rows[$id]);

        return $this;
    }

    public function addColumn(string $column): TemporaryTable
    {
        $this->columns[] = $column;

        foreach ($this->rows as $key => $row) {
            $this->rows[$key][$column] = null;
        }

        return $this;
    }

    public function deleteColumn(string $columnToDelete): bool
    {
        $key = array_search($columnToDelete, $this->columns, true);

        if ($key === false) {
            return false;
        }

        unset($this->columns[$key]);

        foreach ($this->rows as $key => $row) {
            unset($this->rows[$key][$columnToDelete]);
        }

        return true;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getRows(bool $returnObject = false): Generator
    {
        yield $this->rows;
    }

    public function getName(): string
    {
        return 'Temporary';
    }
}
