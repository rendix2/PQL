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
        $database_dir = self::getPath($name);

        if (!is_dir($database_dir)) {
            throw new Exception(sprintf('Database "%s" does not exist.', $name));
        }

        $this->dir = $database_dir;

        $size = 0;

        $files = Finder::findFiles('*')->from($this->dir);

        /**
         * @var $file SplFileInfo
         */
        foreach ($files as $file) {
            $size += $file->getSize();
        }

        $this->size = $size;
        $this->name = $name;

        $this->tables = $this->findTables();

        $this->tablesCount = $files->count();
    }

    /**
     * Database destructor.
     */
    public function __destruct()
    {
        foreach ($this->tables as &$table) {
            $table = null;
        }

        unset($table);

        $this->tables      = null;
        $this->name        = null;
        $this->size        = null;
        $this->tablesCount = null;
        $this->dir         = null;
    }

    /**
     * @param string $name
     *
     * @return string
     *
     */
    public static function getPath($name)
    {
        $sep = DIRECTORY_SEPARATOR;

        return __DIR__ . $sep . self::DATA_DIR . $sep.  $name . $sep;
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
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @return Table[]
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @return Table[]
     */
    public function findTables()
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

    /**
     * @param string $tableName
     *
     * @return Table
     */
    public function getTable($tableName)
    {
        return new Table($this, $tableName);
    }

    /**
     * @return int
     */
    public function calculateDatabaseSize()
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

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @param string $name
     *
     * @return Database
     * @throws Exception
     */
    public static function create($name)
    {
        $path = self::getPath($name);

        if (is_dir($path)) {
            throw new Exception('Database already exists.');
        } else {
            try {
                FileSystem::createDir($path);

                return new Database($name);
            } catch (IOException $e) {
                throw new Exception('Database was not created.');
            }
        }
    }

    /**
     * @param string $name
     *
     * @return Database
     * @throws Exception
     */
    public function delete($name)
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

    /**
     * @param string $newName
     *
     * @return Database
     * @throws Exception
     */
    public function rename($newName)
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

    /**
     * @param Table $table
     *
     * @return bool
     */
    public function tableExists(Table $table)
    {
        foreach ($this->tables as $tableObject) {
            if ($table->getName() === $tableObject->getName()) {
                return true;
            }
        }

        return false;
    }
}
