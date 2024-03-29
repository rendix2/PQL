<?php

namespace pql;

use Exception;

/**
 * Class TableColumn
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class TableColumn
{
    /**
     * @var array
     */
    const COLUMN_TYPES = [self::INTEGER, self::STRING, self::FLOAT, self::BOOL];

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
     * @var bool $unique
     */
    private $unique;

    /**
     * @var Table|null $table
     */
    private $table;

    /**
     * TableColumn constructor.
     *
     * @param string $name
     * @param string $type
     * @param bool $unique
     * @param Table|null $table
     *
     * @throws Exception
     */
    public function __construct($name, $type, $unique, Table $table = null)
    {
        if (!in_array($type, self::COLUMN_TYPES, true)) {
            throw new Exception(sprintf('Unknown type "%s" of column "%s".', $type, $name));
        }
        
        $this->name  = $name;
        $this->type  = $type;
        $this->unique = $unique;
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

    /**
     * @return bool
     */
    public function getUnique()
    {
        return $this->unique;
    }
}
