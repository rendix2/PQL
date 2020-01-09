<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 5. 12. 2019
 * Time: 16:02
 */

/**
 * Class TemporaryTable
 *
 */
class TemporaryTable implements ITable
{
    /**
     * @var array $rows
     */
    private $rows;

    /**
     * @var array $columns
     */
    private $columns;

    /**
     * TemporaryTable constructor.
     *
     * @param array $rows
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;

        if (isset($this->rows[0])) {
            $this->columns = array_keys($this->rows[0]);
        } else {
            $this->columns = array_keys($this->rows);
        }
    }

    /**
     * TemporaryTable destructor.
     */
    public function __destruct()
    {
        $this->rows = null;
        $this->columns = null;
    }

    /**
     * @param array $row
     */
    public function addRow(array $row)
    {
        $this->rows[] = $row;

        $this->columns = array_keys($this->rows);
    }

    /**
     * @param int $id
     */
    public function deleteRow($id)
    {
        unset($this->rows[$id]);
    }

    /**
     * @param string $column
     */
    public function addColumn($column)
    {
        $this->columns[] = $column;

        foreach ($this->rows as $key => $row) {
            $this->rows[$key][$column] = null;
        }
    }

    /**
     * @param string $columnToDelete
     *
     * @return bool
     */
    public function deleteColumn($columnToDelete)
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
     * @return array
     */
    public function getRows($object = false)
    {
        return $this->rows;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Temporary';
    }
}
