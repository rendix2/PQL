<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 30. 1. 2019
 * Time: 16:09
 */

namespace pql;

use Exception;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use SplFileInfo;

/**
 * Class Database
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class Database
{
    /**
     * @var string
     */
    const DATA_DIR = 'data';

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var int $size
     */
    private $size;

    /**
     * @var int $tablesCount
     */
    private $tablesCount;

    /**
     * @var string $dir
     */
    private $dir;

    /**
     * @var Table[] $tables
     */
    private $tables;

    /**
     * Database constructor.
     *
     * @param string $name
     *
     * @throws Exception
     */
    public function __construct($name)
    {
        $databaseDir = self::getPath($name);

        if (!is_dir($databaseDir)) {
            throw new Exception(sprintf('Database "%s" dir "%s"  does not exist.', $databaseDir, $name));
        }

        $this->dir = $databaseDir;

        $size = 0;

        $files = Finder::findFiles('*')->from($this->dir);
        $count = 0;

        /**
         * @var $file SplFileInfo
         */
        foreach ($files as $file) {
            $size += $file->getSize();
            $count++;
        }

        $this->size = $size;
        $this->name = $name;

        $this->tables = $this->findTables();

        $this->tablesCount = $count;
    }

    public static function getPath(string $name): string
    {
        $sep = DIRECTORY_SEPARATOR;

        return __DIR__ . $sep . self::DATA_DIR . $sep.  $name . $sep;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDir(): string
    {
        return $this->dir;
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    public function findTables(): array
    {
        $tables = [];

        /**
         * @var SplFileInfo $file
         */
        $files = Finder::findFiles('*.' . Table::EXT)->from($this->getDir());

        foreach ($files as $file) {
            $extension = $file->getExtension();
            $fileName = str_replace('.' . $extension, '', $file->getFilename());

            $tables[$fileName] = new Table($this, $fileName);
        }

        return $tables;
    }

    public function getTable(string $tableName): Table
    {
        return new Table($this, $tableName);
    }

    public function calculateDatabaseSize(): int
    {
        $size = 0;

        $files = Finder::findFiles('*')->from($this->dir);

        /**
         * @var $file SplFileInfo
         */
        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    public function setSize(int $size): Database
    {
        $this->size = $size;

        return $this;
    }

    public static function create(string $name): Database
    {
        $path = self::getPath($name);

        if (is_dir($path)) {
            throw new Exception('Database already exists.');
        } else {
            try {
                FileSystem::createDir($path);

                return new Database($name);
            } catch (IOException $e) {
                throw new Exception(message: 'Database was not created.', previous: $e);
            }
        }
    }

    public function delete(string $name): Database
    {
        $path = self::getPath($name);
        
        if (!is_dir($path)) {
            throw new Exception(sprintf('Database "%s" not exist', $name));
        }
        
        try {
            FileSystem::delete($path);
            
            return $this;
        } catch (IOException $e) {
            throw new Exception('Database was not created.');
        }
    }

    public function rename(string $newName): Database
    {
        if (!file_exists(self::getPath($this->name))) {
            throw new Exception('Old database does not exists.');
        }

        if (file_exists(self::getPath($newName))) {
            throw new Exception('New database already exists.');
        }

        FileSystem::rename($this->name, $newName);

        return $this;
    }

    public function tableExists(Table $table): bool
    {
        foreach ($this->tables as $tableObject) {
            if ($table->getName() === $tableObject->getName()) {
                return true;
            }
        }

        return false;
    }
}
