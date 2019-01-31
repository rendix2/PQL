<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 30. 1. 2019
 * Time: 16:08
 */

use Nette\Utils\FileSystem;

/**
 * Class Table
 *
 * @author Tomáš Babický tomas.babicky@websta.de
 */
class Table
{
    const EXT = 'pql';

    private $name;

    private $fileName;

    private $size;

    private $rows;
        
    private $database;
    
    private $colums;
    
    private $columnsCount;

    /**
     * Table constructor.
     */
    public function __construct(Database $database, $name)
    {
        $tableFileName = self::getFilePath($database, $name);

        if (!is_file($tableFileName)) {
            throw new Exception('Table does not exist.');
        }

        $fileSize = filesize($tableFileName);

        if ($fileSize === false) {
            throw new Exception('There was problem during counting table size.');
        }
        
        $fileContent = file($tableFileName);
        
        if (!count($fileContent)) {
            FileSystem::write($tableFileName, trim("id"));            
            $fileContent = file($tableFileName);            
        }
        
        $columns = [];
        
        foreach (explode(',', trim($fileContent[0])) as $column) {
            $columns[] = trim($column);
        }

        $this->colums   = $columns;
        $this->columnsCount = count($this->colums);
        $this->name     = $name;
        $this->fileName = $tableFileName;
        $this->size     = $fileSize;
        $this->database = $database;
    }

    /**
     * Table destructor.
     */
    public function __destruct()
    {
        $this->fileName = null;
        $this->name = null;
        $this->size = null;
        $this->rows = null;
        $this->database = null;
    }
    
    public static function create(Database $database, $name, array $columns)
    {
        if (file_exists(self::getFilePath($database, $name))) {
            throw new Exception(sprintf('Table "%s" already exists in database "%s".', $name, $database->getName()));
        }
        
        try {
            FileSystem::write(self::getFilePath($database, $name), implode(', ', $columns));
            
            return true;
        } catch (Nette\IOException $e) {
            return false;
        }
    }
    
    public static function delete(Database $database, $name)
    {
        if (!file_exists(self::getFilePath($database, $name))) {
            throw new Exception(sprintf('Table "%s" does not exist in database "%s".', $name, $database->getName()));
        }
        
        try {
            FileSystem::delete(self::getFilePath($database, $name));
                
            return true;
        } catch (Nette\IOException $e) {
            return false;
        }
    }
    
    public static function getFilePath(Database $database, $name)
    {
        return sprintf('%s%s%s.%s', $database->getPath2(), DIRECTORY_SEPARATOR, $name, self::EXT);
    }

    public function getRows()
    {
        $rows       = file(self::getFilePath($this->database, $this->name));        
        $rowCounter = 0;        
        $rowsObj = [];        
        
        foreach ($rows as $row) {
            if ($rowCounter === 0) {
                $rowCounter++;
                
                continue;
            }            
            
            $rowExploded       = explode(',', $row);            
            $columnValuesArray = [];            
            
            foreach ($this->colums as $columnNumber => $columnValue ) {
                foreach ($rowExploded as $explodedKey => $explodedValue) {
                    if ($columnNumber === $explodedKey) {
                        $columnValuesArray[$columnValue] = trim($explodedValue);
                    }
                }
            }
            
            $rowsObj[]         = new Row($columnValuesArray);
            $columnValuesArray = null;            
            $rowCounter++;
        }
        
        return $rowsObj;        
    }
}
