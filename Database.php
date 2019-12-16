<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 30. 1. 2019
 * Time: 16:09
 */

use Nette\Utils\FileSystem;
use Nette\Utils\Finder;

/**
 * Class Database
 *
 * @author rendix2
 */
class Database
{
    /**
     * @var string
     */
    const DATABASE_DIR = '%s' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR. '%s';

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
     * @var string $databaseDir
     */
    private $databaseDir;

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

        $this->databaseDir = $database_dir;

        $size = 0;

        $files = Finder::findFiles('*')->from($this->databaseDir);

        /**
         * @var $file SplFileInfo
         */
        foreach ($files as $file) {
            $size += $file->getSize();
        }

        $this->size = $size;
        $this->name = $name;

        $this->tables = [];

        $this->tablesCount = $files->count();
    }

    /**
     * Database destructor.
     */
    public function __destruct()
    {
        $this->tables      = null;
        $this->name        = null;
        $this->size        = null;
        $this->tablesCount = null;
        $this->databaseDir = null;
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
    public function getDatabaseDir()
    {
        return $this->databaseDir;
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
        $files = Finder::findFiles('*.' . Table::EXT)->from($this->getDatabaseDir());

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

        $files = Finder::findFiles('*')->from($this->databaseDir);

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
            } catch (Nette\IOException $e) {
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
        } catch (Nette\IOException $e) {
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
}
