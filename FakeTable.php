<?php

class FakeTable implements ITable
{
    
    /**
     * 
     * @var array $rows
     */
    private $rows;
    
    /**
     * 
     * @var array $columns
     */
    private $columns;
    
    public function __construct(array $rows, array $columns)
    {
        $this->rows    = $rows;
        $this->columns = $columns;
    }
    
    /**
     * 
     */
    public function __destruct()
    {
        $this->rows    = null;
        $this->columns = null;
    }
    
    /**
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

