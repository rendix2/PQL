<?php

class Column
{
    const COLUMN_TYPE = ['int', 'string', 'float'];
    
    private $name;
    
    private $type;
    
    public function __construct($name, $type)
    {
        if (!in_array($type, self::COLUMN_TYPE, true)) {
            throw new Exception(sprintf('Unknown type "%s" of column "%s".', $type, $name));
        }
        
        $this->name = $name;
        $this->type = $type;
    }
    
    public function getName()
    {
        return $this->name;    
    }
    
    public function getType()
    {
        return $this->type;
    }
}

