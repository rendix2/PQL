<?php

/**
 * Class TableColumn
 */
class TableColumn
{
    /**
     * @var array
     */
    const COLUMN_TYPES = ['int', 'string', 'float', 'bool'];

    /**
     * @var string
     */
    const INTEGER = 'int';

    /**
     * @var string
     */
    const STRING = 'string';

    /**
     * @var string
     */
    const FLOAT = 'float';

    /**
     * @var string
     */
    const BOOL = 'bool';

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
     * @var Table|null $table
     */
    private $table;

    /**
     * TableColumn constructor.
     *
     * @param string     $name
     * @param string     $type
     * @param Table|null $table
     *
     * @throws Exception
     */
    public function __construct($name, $type, Table $table = null)
    {
        if (!in_array($type, self::COLUMN_TYPES, true)) {
            throw new Exception(sprintf('Unknown type "%s" of column "%s".', $type, $name));
        }
        
        $this->name = $name;
        $this->type = $type;
        $this->table = $table;
    }

    /**
     * TableColumn destructor.
     */
    public function __destruct()
    {
        $this->name = null;
        $this->type = null;
        $this->table = null;
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

    /**
     * @return Table|null
     */
    public function getTable()
    {
        return $this->table;
    }
}

