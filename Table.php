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
 * @author rendix2
 */
class Table implements ITable
{
    const EXT = 'pql';

    const FIRST_LINE_LENGTH = 102400;

    const COLUMN_DELIMITER = ', ';

    const COLUMN_DATA_DELIMITER = ':';

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
     * @var int $rowsCount
     */
    private $rowsCount;

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
            FileSystem::write($tableFileName, trim(sprintf('id%sint', self::COLUMN_DATA_DELIMITER)));
            $fileContent = file($tableFileName);            
        }
        
        $columns     = [];
        $columnNames = explode(self::COLUMN_DELIMITER, trim($fileContent[0]));
        
        foreach ($columnNames as $column) {
            $columnExploded = explode(self::COLUMN_DATA_DELIMITER, trim($column));
            $columns[]      = new Column($columnExploded[0], $columnExploded[1]);
        }

        $this->columns      = $columns;
        $this->columnsCount = count($this->columns);
        $this->name         = $name;
        $this->fileName     = $tableFileName;
        $this->size         = $fileSize;
        $this->database     = $database;
        $this->rowsCount    = count($fileContent) - 1;
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
        $this->rowsCount    = null;
        $this->database     = null;
        $this->columns      = null;
        $this->columnsCount = null;
    }

    /**
     * @return int
     */
    public function getRowsCount()
    {
        return $this->columnsCount;
    }

    /**
     * @param string $column
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
    public function getFileName()
    {
        return $this->fileName;
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
            
            FileSystem::write(self::getFilePath($database, $name), implode(self::COLUMN_DELIMITER, $columnsNames));
            
            return true;
        } catch (Nette\IOException $e) {
            return false;
        }
    }

    /**
     * @param string $name
     * @param string $type
     *
     * @return Table
     * @throws Exception
     */
    public function addColumn($name, $type)
    {
        if(!in_array($type, Column::COLUMN_TYPE, true)) {
            throw new Exception('Unknown column type.');
        }

        $handle   = fopen($this->fileName,'r+b');
        $firstRow = '';

        while ($char = fread($handle, self::FIRST_LINE_LENGTH) !== "\n") {
            $firstRow .= $char;
        }

        $newsInFirstRow        = sprintf(', %s%s%s', $name, self::COLUMN_DATA_DELIMITER, $type);
        $firstRow             .= $newsInFirstRow;
        $newsInFirstRowLength  = mb_strlen($newsInFirstRow);

        fwrite($handle, $firstRow);

        $i   = 0;
        $row = '';

        $fileSize = $this->size + $newsInFirstRowLength;

        while ($char = fread($handle, $fileSize)) {
            $row .= $char;

            if ($char === "\n" && $i > 1) {
                $i++;

                fwrite($handle, $row . self::COLUMN_DELIMITER);
                $row = '';
            }
        }
        $size = filesize($this->fileName);

        if ($size === false) {
            throw new Exception('There was problem during counting table size.');
        }

        $this->size = $size;
        fclose($handle);

        return $this;
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
     * @param bool $object
     *
     * @return Row[]|array
     * @throws Exception
     */
    public function getRows($object = false)
    {
        $rows       = $this->toArray();
        $rowCounter = 0;        
        $rowsObj    = [];
        
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
                        } elseif ( $columnValue->getType() === 'bool') {
                            $columnValuesArray[$columnValue->getName()] = (bool)trim($explodedValue);
                        } else {
                            throw new Exception(sprintf('Column "%s" using unknown type "%s".', $columnValue->getName(), $columnValue->getType()));
                        }
                    }
                }
            }
            
            if ($object) {
                $rowsObj[] = new Row($columnValuesArray);
            } else {
                $rowsObj[] = $columnValuesArray;
            }
            
            $columnValuesArray = null;            
            $rowCounter++;
        }
        
        return $rowsObj;        
    }

    /**
     * @return array|bool
     */
    public function toArray()
    {
        return file(self::getFilePath($this->database, $this->name));
    }
}
