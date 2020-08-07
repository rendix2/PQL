<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 30. 1. 2019
 * Time: 16:08
 */

namespace pql\QueryRow;

use stdClass;

/**
 * Class TaleRow
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class TableRow implements IRow
{
    /**
     * @var stdClass $row
     */
    private $row;

    /**
     * @var array $rawData
     */
    private $rawData;

    /**
     * @var array $columns
     */
    private $columns;

    /**
     * @var int $columnsCount
     */
    private $columnsCount;

    /**
     * TableRow constructor.
     *
     * @param $data
     */
    public function __construct(array $data)
    {
        $this->row = new stdClass();

        $this->rawData = $data;
        $this->columns = array_keys($data);

        $this->columnsCount = count($this->columns);
        
        foreach ($data as $key => $value) {
            $this->row->{$key} = $value;
        }
    }

    /**
     * Row destructor.
     */
    public function __destruct()
    {
        $this->row          = null;
        $this->rawData      = null;
        $this->columns      = null;
        $this->columnsCount = null;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->row->{$name});
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->row->{$name});
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->row->{$name} = $value;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->row->{$name};
    }

    /**
     * @return stdClass
     */
    public function get()
    {
        return $this->row;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return int
     */
    public function getColumnsCount()
    {
        return $this->columnsCount;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->rawData;
    }
}
