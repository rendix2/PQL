<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 30. 1. 2019
 * Time: 16:08
 */

namespace pql;

use Exception;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use SplFileInfo;
use SplFileObject;

/**
 * Class Table
 *
 * @author rendix2 <rendix2@seznam.cz>
 * @package pql
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
     * @var TableColumn[] $columns
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
     * @var string $lineEnds
     */
    private $lineEnds;

    /**
     * @var int $lineEndsLength
     */
    private $lineEndsLength;

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

        $this->name = $name;

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

        $indexes = Finder::findFiles('*.' .self::INDEX_EXTENSION)->in($this->indexDir);

        /**
         * @var SplFileInfo $index
         */
        foreach ($indexes as $index) {
            $fileName = str_replace('.' . self::INDEX_EXTENSION, '', $index->getFilename());

            //$this->indexes[$fileName] = BtreeJ::read($this->indexDir . $index->getFilename());
            $this->indexes[$fileName] = $index->getFilename();
        }

        $fileContent = file($filePath);
        $columns     = [];
        $columnNames = explode(self::COLUMN_DELIMITER, trim($fileContent[0]));

        foreach ($columnNames as $column) {
            $columnExploded = explode(self::COLUMN_DATA_DELIMITER, trim($column));
            $columns[]      = new TableColumn($columnExploded[0], $columnExploded[1], $this);
        }

        $this->columns       = $columns;
        $this->columnsCount  = count($this->columns);
        $this->size          = $fileSize;
        $this->rowsCount     = count($fileContent) - 1;

        // check line ends
        if (preg_match("#\\r\\n$#", $fileContent[0])) {
            $this->columnsString = substr($fileContent[0], 0, -2);
            $this->lineEnds = "\r\n";
        } elseif (preg_match("#\\n\\r$#", $fileContent[0])) {
            $this->columnsString = substr($fileContent[0], 0, -2);
            $this->lineEnds = "\n\r";
        } elseif (preg_match("#\\r$#", $fileContent[0])) {
            $this->columnsString = substr($fileContent[0], 0, -1);
            $this->lineEnds = "\r";
        } elseif (preg_match("#\\n$#", $fileContent[0])) {
            $this->columnsString = substr($fileContent[0], 0, -1);
            $this->lineEnds = "\n";
        }

        $this->lineEndsLength = mb_strlen($this->lineEnds);
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

        foreach ($this->columns as &$column) {
            $column = null;
        }

        unset($column);

        $this->columns       = null;
        $this->columnsCount  = null;
        $this->columnsString = null;
        $this->filePath      = null;
        $this->tableDir      = null;
        $this->indexDir      = null;
        $this->indexes       = null;
        $this->lineEnds      = null;
        $this->lineEndsLength = null;
    }

    /**
     * @return int
     */
    public function getRowsCount()
    {
        return $this->columnsCount;
    }

    /**
     * @return TableColumn[]
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
     * @return string
     */
    public function getFileEnds()
    {
        return $this->lineEnds;
    }

    /**
     * @return int
     */
    public function getFileEndsLength()
    {
        return $this->lineEndsLength;
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

        return $database->getDir() . $name . $sep;
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

        return $this->database->getDir() . $sep . $this->name . $sep;
    }

    /**
     * @param string $column
     *
     * @return bool
     */
    public function columnExists($column)
    {
        foreach ($this->getColumns() as $columnObject) {
            if ($columnObject->getName() === $column) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Database  $database
     * @param string    $name
     * @param TableColumn[]  $columns
     *
     * @return Table
     * @throws Exception
     */
    public static function create(Database $database, $name, array $columns)
    {
        if (file_exists(self::getFilePathFromDatabase($database, $name))) {
            $message = sprintf('Table "%s" already exists in database "%s".', $name, $database->getName());

            throw new Exception($message);
        }

        $columnsNames = [];

        /**
         * @var TableColumn $column
         */
        foreach ($columns as $column) {
            if ($column instanceof TableColumn) {
                $columnsNames[] = sprintf('%s:%s', $column->getName(), $column->getType());
            } else {
                throw new Exception('Unknown param "$columns". It should be instance of TableColumn class.');
            }
        }

        FileSystem::write(
            self::getFilePathFromDatabase($database, $name),
            implode(self::COLUMN_DELIMITER, $columnsNames)
        );

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
        if ($this->columnExists($name)) {
            $message = sprintf('Table "%s" already has column "%s".', $this->name, $name);

            throw new Exception($message);
        }

        if (!in_array($type, TableColumn::COLUMN_TYPES, true)) {
            $message = sprintf('Unknown "%s" column type.', $type);

            throw new Exception($message);
        }

        $newColumn = self::COLUMN_DELIMITER . $name . self::COLUMN_DATA_DELIMITER . $type;
        $firstRow = $this->columnsString . $newColumn . $this->lineEnds;
        $tmpFileName = $this->getFilePath() . '.tmp';

        $file = new SplFileObject($tmpFileName, 'a');

        $file->fwrite($firstRow);
        $lastColumn = $this->columns[$this->columnsCount - 1]->getName();

        foreach ($this->getRows() as $row) {
            $row[$lastColumn] = str_replace(["\r", "\n", "\r\n", PHP_EOL], '', $row[$lastColumn]);

            $file->fwrite(implode(self::COLUMN_DELIMITER, $row) . ', null' . $this->lineEnds);
        }

        $file = null;

        $tmp = file_get_contents($tmpFileName);
        file_put_contents($this->getFilePath(), $tmp);
        FileSystem::delete($tmpFileName);

        $size = filesize($this->filePath);

        if ($size === false) {
            throw new Exception('There was problem during counting table size.');
        }

        $this->database->setSize($this->database->calculateDatabaseSize());
        $this->size = $size;
        $this->columns[] = new TableColumn($name, $type, $this);
        $this->columnsString = $firstRow;
        $this->columnsCount++;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Table
     * @throws Exception
     */
    public function deleteColumn($name)
    {
        // initial checks
        if (!$this->columnsCount) {
            $message = sprintf('Table %s does not have any column.', $this->name);

            throw new Exception($message);
        }

        if (!$this->columnExists($name)) {
            $message = sprintf('Column "%s" in table "%s" does not exist.', $name, $this->name);

            throw new Exception($message);
        }

        // remove column
        $explodedColumns = explode(self::COLUMN_DELIMITER, $this->columnsString);
        $column2Delete = 0;

        foreach ($explodedColumns as $column) {
            $columnName = explode(self::COLUMN_DATA_DELIMITER, $column);

            if ($columnName[TableColumn::COLUMN_NAME] === $name) {
                unset($explodedColumns[$column2Delete]);
                break;
            }

            $column2Delete++;
        }

        $file = file($this->filePath);
        $newRowsString = '';

        foreach ($file as $keyRow => $row) {
            $row = str_replace(["\r", "\n", "\r\n", PHP_EOL], '', $row);

            $columns = explode(self::COLUMN_DELIMITER, $row);
            unset($columns[$column2Delete]);
            $newRowsString .= implode(self::COLUMN_DELIMITER, $columns);
            $newRowsString .= PHP_EOL;
        }

        //return;
        $result = file_put_contents($this->filePath, $newRowsString);

        // recalculate data
        $this->columnsString = implode(self::COLUMN_DELIMITER, $explodedColumns);
        $this->size = filesize($this->filePath);
        $this->columnsCount--;

        foreach ($this->columns as $columnKey => $column) {
            if ($column->getName() === $name) {
                unset($this->columns[$columnKey]);
            }
        }

        $this->columns = array_values($this->columns);
        $database_size = $this->database->calculateDatabaseSize();
        $this->database->setSize($database_size);

        return $this;
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return Table
     */
    public function renameColumn($from, $to)
    {
        return $this;
    }

    /**
     * @return Table
     */
    public function truncate()
    {
        $firstRow = $this->columnsString . $this->lineEnds;

        $file = new SplFileObject($this->filePath, 'wb');

        $file->fwrite($firstRow);
        $file = null;

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
             $message = sprintf(
                 'Table "%s" does not exist in database "%s".',
                 $this->name,
                 $this->database->getName()
             );

            throw new Exception($message);
        }
        
        try {
            FileSystem::delete($this->filePath);
                
            return true;
        } catch (IOException $e) {
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
        $fileRows = $this->toArray();
        unset($fileRows[0]);

        $columnNames = [];

        foreach ($this->columns as $column) {
            $columnNames[] = $column->getName();
        }

        $resultRows = [];
        
        foreach ($fileRows as $row) {
            $row = str_replace($this->lineEnds, '', $row);

            $exploded = explode(self::COLUMN_DELIMITER, $row);
            $columnValuesArray = array_combine($columnNames, $exploded);

            foreach ($this->columns as $column) {
                switch ($column->getType()) {
                    case TableColumn::BOOL:
                        $columnValuesArray[$column->getName()] = (bool) $columnValuesArray[$column->getName()];
                        break;
                    case TableColumn::FLOAT:
                        $columnValuesArray[$column->getName()] = (float) $columnValuesArray[$column->getName()];
                        break;
                    case TableColumn::INTEGER:
                        $columnValuesArray[$column->getName()] = (int) $columnValuesArray[$column->getName()];
                        break;
                    case TableColumn::STRING:
                        $columnValuesArray[$column->getName()] = (string)$columnValuesArray[$column->getName()];
                        break;
                    default:
                        $message = sprintf('Unknown column type "%s".', $column->getType());

                        throw new Exception($message);
                }
            }

            if ($object) {
                $resultRows[] = new Row($columnValuesArray);
            } else {
                $resultRows[] = $columnValuesArray;
            }
        }
        
        return $resultRows;
    }

    /**
     * @return array|bool
     */
    public function toArray()
    {
        return file($this->filePath);
    }
}
