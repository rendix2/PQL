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

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $fileName
     */
    private $fileName;

    /**
     * @var int $size
     */
    private $size;

    /**
     * @var Row[] $rows
     */
    private $rows;

    /**
     * @var Database $database
     */
    private $database;

    /**
     * @var array $columns
     */
    private $columns;

    /**
     * @var int $columnsCount
     */
    private $columnsCount;

    /**
     * Table constructor.
     *
     * @param Database $database
     * @param string   $name
     *
     * @throws Exception
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

        $this->columns      = $columns;
        $this->columnsCount = count($this->columns);
        $this->name         = $name;
        $this->fileName     = $tableFileName;
        $this->size         = $fileSize;
        $this->database     = $database;
    }

    /**
     * Table destructor.
     */
    public function __destruct()
    {
        $this->name         = null;
        $this->fileName     = null;
        $this->size         = null;
        $this->rows         = null;
        $this->database     = null;
        $this->columns      = null;
        $this->columnsCount = null;
    }

    /**
     * @param $column
     *
     * @return bool
     */
    public function columnExists($column)
    {
        return in_array($column, $this->getColumns(),true);
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Database $database
     * @param string   $name
     * @param array    $columns
     *
     * @return bool
     * @throws Exception
     */
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

    /**
     * @param Database $database
     * @param string   $name
     *
     * @return bool
     * @throws Exception
     */
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

    /**
     * @param Database $database
     * @param string   $name
     *
     * @return string
     */
    public static function getFilePath(Database $database, $name)
    {
        return sprintf('%s%s%s.%s', $database->getPath2(), DIRECTORY_SEPARATOR, $name, self::EXT);
    }

    /**
     * @return Row[]
     */
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
            
            foreach ($this->columns as $columnNumber => $columnValue ) {
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
