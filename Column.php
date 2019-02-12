<?php

class Column
{
    /**
     * @var array
     */
    const COLUMN_TYPE = ['int', 'string', 'float', 'bool'];

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $type
     */
    private $type;

    /**
     * Column constructor.
     *
     * @param string $name
     * @param string $type
     *
     * @throws Exception
     */
    public function __construct($name, $type)
    {
        if (!in_array($type, self::COLUMN_TYPE, true)) {
            throw new Exception(sprintf('Unknown type "%s" of column "%s".', $type, $name));
        }
        
        $this->name = $name;
        $this->type = $type;
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

