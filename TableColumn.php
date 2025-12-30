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
    public const array COLUMN_TYPES = [self::INTEGER, self::STRING, self::FLOAT, self::BOOL];

    /**
     * @var string
     */
    public const string INTEGER = 'int';

    /**
     * @var string
     */
    public const string STRING = 'string';

    /**
     * @var string
     */
    public const string FLOAT = 'float';

    /**
     * @var string
     */
    public const string BOOL = 'bool';

    public const int COLUMN_NAME = 0;

    private const int COLUMN_TYPE = 1;

    private string $name;

    private string $type;

    private bool $unique;

    private ?Table $table;

    public function __construct(string $name, string $type, bool $unique, ?Table $table = null)
    {
        if (!in_array($type, self::COLUMN_TYPES, true)) {
            throw new Exception(sprintf('Unknown type "%s" of column "%s".', $type, $name));
        }
        
        $this->name  = $name;
        $this->type  = $type;
        $this->unique = $unique;
        $this->table = $table;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTable(): ?Table
    {
        return $this->table;
    }

    public function getUnique(): bool
    {
        return $this->unique;
    }
}
