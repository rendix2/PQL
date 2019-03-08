<?php

class FakeTable implements ITable
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
     * FakeTable constructor.
     *
     * @param array $rows
     * @param array $columns
     */
    public function __construct(array $rows, array $columns)
    {
        $this->rows    = $rows;
        $this->columns = $columns;
    }
    
    /**
     * FakeTable destructor.
     */
    public function __destruct()
    {
        $this->rows    = null;
        $this->columns = null;
    }

    /**
     * @param bool $object
     *
     * @return array
     */
    public function getRows($object = false)
    {
        return  $this->rows;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }
}

