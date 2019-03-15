<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 30. 1. 2019
 * Time: 16:08
 */

use Nette\Utils\FileSystem;
use Nette\Utils\Finder;

/**
 * Class Table
 *
 * @author rendix2
 */
class Table implements ITable
{
    /**
     * @var string
     */
    const EXT = 'pql';

    /**
     * @var string
     */
    const INDEX_DIR = 'index';

    /**
     * @var string
     */
    const INDEX_EXTENSION = 'index';

    /**
     * @var int
     */
    const FIRST_LINE_LENGTH = 102400;

    /**
     * @var string
     */
    const COLUMN_DELIMITER = ', ';

    /**
     * @var string
     */
    const COLUMN_DATA_DELIMITER = ':';

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $fileName
     */
    private $filePath;

    /**
     * @var string $tableDir
     */
    private $tableDir;

    /**
     * @var string $indexDir
     */
    private $indexDir;

    /**
     *
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
     * @var string $columnsString
     */
    private $columnsString;

    /**
     * @var array $indexes
     */
    private $indexes;

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
        $this->database = $database;
        $this->fileName = $name . '.' . self::EXT;

        $sep = DIRECTORY_SEPARATOR;

        $tableDir = self::getFilePathFromDatabase($database, $name);

        if (!file_exists($tableDir)) {
            $message = sprintf('Directory of table "%s" does not exist.', $name);

            throw new Exception($message);
        }

        $this->tableDir = $tableDir;

        $filePath = $tableDir . $this->fileName;

        if (!file_exists($filePath)) {
            throw new Exception(sprintf('File of table "%s" does not exist.', $name));
        }

        $this->filePath = $filePath;
        $this->name     = $name;

        $fileSize = filesize($filePath);

        if ($fileSize === false) {
            throw new Exception('There was problem during counting table size.');
        }

        if ($fileSize === 0) {
            FileSystem::write($filePath, trim(sprintf('id%sint', self::COLUMN_DATA_DELIMITER)));
        }

        $index_dir = $tableDir . 'index' . $sep;

        if (!file_exists($index_dir)) {
            throw new Exception('Index dir does not exist.');
        }

        $this->indexDir = $index_dir;
        $this->indexes  = [];

        /**
         * @var SplFileInfo $index
         */
        foreach (Finder::findFiles('*.' .self::INDEX_EXTENSION)->in($this->indexDir) as $index) {
            $fileName = str_replace('.' . self::INDEX_EXTENSION, '', $index->getFilename());

            //$this->indexes[$fileName] = BtreeJ::read($this->indexDir . $index->getFilename());
            $this->indexes[$fileName] = $index->getFilename();
        }

        $fileContent = file($filePath);
        $columns     = [];
        $columnNames = explode(self::COLUMN_DELIMITER, trim($fileContent[0]));
        
        foreach ($columnNames as $column) {
            $columnExploded = explode(self::COLUMN_DATA_DELIMITER, trim($column));
            $columns[]      = new Column($columnExploded[0], $columnExploded[1]);
        }

        $this->columns       = $columns;
        $this->columnsCount  = count($this->columns);
        $this->size          = $fileSize;
        $this->rowsCount     = count($fileContent) - 1;
        $this->columnsString = substr($fileContent[0],0, -2); // remove new line
    }

    /**
     * Table destructor.
     */
    public function __destruct()
    {
        $this->name          = null;
        $this->fileName      = null;
        $this->size          = null;
        $this->rows          = null;
        $this->rowsCount     = null;
        $this->database      = null;
        $this->columns       = null;
        $this->columnsCount  = null;
        $this->columnsString = null;
        $this->filePath      = null;
        $this->tableDir      = null;
        $this->indexDir      = null;
        $this->indexes       = null;
    }

    /**
     * @return int
     */
    public function getRowsCount()
    {
        return $this->columnsCount;
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
     * @return string
     */
    public function getColumnsString()
    {
        return $this->columnsString;
    }

    /**
     * @return array
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getTableDir()
    {
        return $this->tableDir;
    }

    /**
     * @param Database $database
     * @param string   $name
     *
     * @return string
     */
    public static function getFilePathFromDatabase(Database $database, $name)
    {
        $sep = DIRECTORY_SEPARATOR;

        return $database->getDatabaseDir() . $name . $sep;
    }

    /**
     * @return string
     */
    public function getIndexDir()
    {
        return $this->indexDir;
    }

    /**
     * @return string
     */
    public function getDirPath()
    {
        $sep = DIRECTORY_SEPARATOR;

        return $this->database->getDatabaseDir() . $sep . $this->name . $sep;
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
     * @param Database  $database
     * @param string    $name
     * @param Column[]  $columns
     *
     * @return Table
     * @throws Exception
     */
    public static function create(Database $database, $name, array $columns)
    {
        if (file_exists(self::getFilePathFromDatabase($database, $name))) {
            throw new Exception(sprintf('Table "%s" already exists in database "%s".', $name, $database->getName()));
        }

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

        FileSystem::write(self::getFilePathFromDatabase($database, $name),
            implode(self::COLUMN_DELIMITER, $columnsNames));

        return new Table($database, $name);
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
     *
     * @return bool
     * @throws Exception
     */
    public function delete()
    {
        if (!file_exists($this->filePath)) {
             $message = sprintf('Table "%s" does not exist in database "%s".',
                 $this->name,
                 $this->database->getName()
             );

            throw new Exception($message);
        }
        
        try {
            FileSystem::delete($this->filePath);
                
            return true;
        } catch (Nette\IOException $e) {
            return false;
        }
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
            
            $rowExploded       = explode(Table::COLUMN_DELIMITER, $row);            
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
        return file($this->filePath);
    }
}
