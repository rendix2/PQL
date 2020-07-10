<?php


namespace pql\QueryBuilder;


class PFunction
{

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $column
     */
    private $column;

    /**
     * @var array $arguments
     */
    private $arguments;

    /**
     * PFunction constructor.
     * @param string $name
     * @param string $column
     * @param array $arguments
     */
    public function __construct($name, $column, $arguments = [])
    {
        $this->name = $name;
        $this->column = $column;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (count($this->arguments)) {
            return $this->name . '(' . $this->column . ', '. implode(',', $this->arguments) . ')';
        } else {
            return $this->name . '(' . $this->column . ')';
        }
        
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}