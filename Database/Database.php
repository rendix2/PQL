<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Database.php
 * User: Tomáš Babický
 * Date: 26.08.2021
 * Time: 22:06
 */

namespace PQL\Database;

use Exception;
use Nette\Utils\Finder;
use PQL\Database\Query\Builder\DeleteBuilder;
use PQL\Database\Query\Builder\InsertBuilder;
use PQL\Database\Query\Builder\SelectBuilder;
use PQL\Database\Query\Builder\UpdateBuilder;
use SplFileInfo;

/**
 * Class Database
 *
 * @package PQL\Database
 */
class Database
{
    /**
     * @var string $name
     */
    private string $name;

    /**
     * @var string $directory
     */
    private string $directory;

    /**
     * @var array $tables
     */
    private array $tables;

    /**
     * Database constructor.
     *
     * @param string $name
     *
     * @throws Exception
     */
    public function __construct(string $name)
    {
        $sep = DIRECTORY_SEPARATOR;
        $databaseDirectory = __DIR__ .  $sep. '..'. $sep . 'data' . $sep . $name;

        // Our database exists
        if (is_dir($databaseDirectory) && file_exists($databaseDirectory)) {
            $this->name = $name;
            $this->directory = $databaseDirectory;
        } else {
            $message = sprintf('Database %s does not exists', $name);

            throw new Exception($message);
        }

        $directoryTables = Finder::findDirectories('*')->from($this->directory);

        /**
         * @var SplFileInfo $directoryTable
         */
        foreach ($directoryTables as $directoryTable) {
            $tableName = $directoryTable->getFilename();

            $this->tables[$tableName] = new Table($this, $tableName, $directoryTable->getPathname());
        }

        //exit;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return Table[]
     */
    public function getTables() : array
    {
        return $this->tables;
    }

    /**
     * @throws Exception
     */
    public function getTable(string $name) : Table
    {
        if (isset($this->tables[$name])) {
            return $this->tables[$name];
        } else {
            $message = sprintf('Table "%s" was not found in database "%s".', $name, $this->name);

            throw new Exception($message);
        }
    }

    public function selectQuery() : SelectBuilder
    {
        return new SelectBuilder($this);
    }
    
    public function insertQuery() : InsertBuilder
    {
        return new InsertBuilder($this);
    }

    public function deleteQuery() : DeleteBuilder
    {
        return new DeleteBuilder($this);
    }

    public function updateQuery() : UpdateBuilder
    {
        return new UpdateBuilder($this);
    }
}