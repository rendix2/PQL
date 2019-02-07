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
    const DATABASE_DIR = '%s' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR. '%s';

    private $name;

    private $size;

    private $tablesCount;

    /**
     * Database constructor.
     */
    public function __construct($name)
    {
        if (!is_dir(self::getPath($name))) {
            throw new Exception('Database does not exist.');
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

        $this->size        = $size;
        $this->name        = $name;
        $this->tablesCount = $files->count();
    }

    /**
     * Database destructor.
     */
    public function __destruct()
    {
        $this->name = null;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function getPath2()
    {
        return self::getPath($this->name);
    }
    
    public static function getPath($name)
    {
        return sprintf(self::DATABASE_DIR,__DIR__, $name);
    }

    /**
     * @return Table[]
     */
    public function getTables()
    {
        $mask   = sprintf('*.%s', Table::EXT);
        $tables = [];

        foreach (Finder::findFiles($mask)->in(self::getPath($this->name)) as $file) {            
            $extension = $file->getExtension();            
            $fileName = str_replace('.' . $extension, '', $file->getFileName());
            
            $tables[$fileName] = new Table($this, $fileName);                        
        }

        return $tables;
    }

    public static function create($name)
    {
        $path = self::getPath($name);        

        if (is_dir($path)) {
            throw new Exception('Database already exists.');
        } else {
            
            try {
                FileSystem::createDir($path);
                
                return true;
            } catch (Nette\IOException $e) {
                throw new Exception('Database was not created.');                
            }
        }

        return new Database($name);
    }

    public function delete($name)    
    {
        $path = self::getPath($name);
        
        if (!is_dir($path)) {
            throw new Exception(sprintf('Database "%s" not exist', $name));
        }
        
        try {
            FileSystem::delete($path);
            
            return true;
        } catch (Nette\IOException $e) {
            throw new Exception('Database was not created.');
        }
    }
}
