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
class Table implements ITable
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
            FileSystem::write($tableFileName, trim("id:int"));            
            $fileContent = file($tableFileName);            
        }
        
        $columns = [];
        
        foreach (explode(', ', trim($fileContent[0])) as $column) {
            $columnExploded = explode(':', trim($column));            
            $columns[]      = new Column($columnExploded[0], $columnExploded[1]);
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
        foreach ($this->getColumns() as $columnObject) {
            if ($columnObject->getName() == $column) {
                return true;
            }
        }        
        
        return false;
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
     * @param Database  $database
     * @param string    $name
     * @param Column[]  $columns
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
            $columnsNames = [];
            
            /**
             * @var Column $column
             */
            foreach ($columns as $column) {
                if ($column instanceof Column) {
                    $columnsNames[] = sprintf('%s:%s', $column->getName(), $column->getType());
                } else {
                    throw new Exception('Unknown param "$columns". It should be instance of Column class.');
                }
            }
            
            FileSystem::write(self::getFilePath($database, $name), implode(', ', $columnsNames));
            
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
     * @return Row[]|array
     */
    public function getRows($object = false)
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
                        if ($columnValue->getType() === 'int') {
                            $columnValuesArray[$columnValue->getName()] = (int)trim($explodedValue);
                        } elseif($columnValue->getType() === 'string') {
                            $columnValuesArray[$columnValue->getName()] = (string)trim($explodedValue);
                        }  elseif($columnValue->getType() === 'float') {
                            $columnValuesArray[$columnValue->getName()] = (float)trim($explodedValue);
                        } else {
                            throw new Exception(sprintf('Column "%s" using unknow type "%s".', $columnValue->getName(), $columnValue->getType()));
                        }
                    }
                }
            }
            
            if ($object) {
                $rowsObj[]         = new Row($columnValuesArray);
            } else {
                $rowsObj[] = $columnValuesArray;
            }
            
            $columnValuesArray = null;            
            $rowCounter++;
        }
        
        return $rowsObj;        
    }
}
