<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Database.php
 * User: Tomáš Babický
 * Date: 26.08.2021
 * Time: 22:06
 */

namespace PQL;


use Exception;
use Nette\Utils\Finder;
use PQL\Query\Builder\Select;
use SplFileInfo;

class Database
{
    private string $name;

    private string $directory;

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
            $this->tables[$directoryTable->getFilename()] = new Table($this, $directoryTable->getFilename(), $directoryTable->getPathname());

            $entityGenerator = new EntityGenerator($this->tables[$directoryTable->getFilename()]);
            $entityGenerator->run();
        }
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

    public function getTable(string $name) : Table
    {
        if (isset($this->tables[$name])) {
            return $this->tables[$name];
        } else {
            $message = sprintf('Table "%s" was not found.', $name);

            throw new Exception($message);
        }
    }

    public function selectQuery() : Select
    {
        return new Select($this);
    }
}