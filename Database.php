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
        if (!is_dir(self::getPath($name))) {
            throw new Exception(sprintf('Database "%s" does not exist.', $name));
        }

        $mask = sprintf('*.%s', Table::EXT);
        $size = 0;
        
        $files = Finder::findFiles($mask)->in(self::getPath($name));

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
    public function getPath2()
    {
        return self::getPath($this->name);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function getPath($name)
    {
        return sprintf(self::DATABASE_DIR,__DIR__, $name);
    }

    /**
     * @return Table[]
     */
    public function getTables()
    {
        if ($this->tables) {
            return $this->tables;
        }

        $mask   = sprintf('*.%s', Table::EXT);
        $tables = [];

        /**
         * @var SplFileInfo $file
         */
        foreach (Finder::findFiles($mask)->in(self::getPath($this->name)) as $file) {            
            $extension = $file->getExtension();            
            $fileName = str_replace('.' . $extension, '', $file->getFilename());
            
            $tables[$fileName] = new Table($this, $fileName);                        
        }

        return $this->tables = $tables;
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
     */
    public function rename($newName)
    {
        self::renameS($this->name, $newName);

        return $this;
    }

    /**
     * @param string oldName
     * @param string $newName
     *
     * @throws Exception
     */
    public static function renameS($oldName, $newName)
    {
        if (!file_exists(self::getPath($oldName))) {
            throw new Exception('Old database does not exists.');
        }

        if (file_exists(self::getPath($newName))) {
            throw new Exception('New database already exists.');
        }

        FileSystem::rename($oldName, $newName);
    }
}
