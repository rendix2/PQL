<?php

class Column
{
    /**
     * @var array
     */
    const COLUMN_TYPES = ['int', 'string', 'float', 'bool'];

    /**
     * @var int
     */
    const COLUMN_NAME = 0;

    /**
     * @var int
     */
    const COLUMN_TYPE = 1;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var Table $table
     */
    private $table;

    /**
     * Column constructor.
     *
     * @param string $name
     * @param string $type
     * @param Table  $table
     *
     * @throws Exception
     */
    public function __construct($name, $type, Table $table)
    {
        if (!in_array($type, self::COLUMN_TYPES, true)) {
            throw new Exception(sprintf('Unknown type "%s" of column "%s".', $type, $name));
        }
        
        $this->name = $name;
        $this->type = $type;
        $this->table = $table;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;    
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}

