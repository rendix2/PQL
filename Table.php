<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 30. 1. 2019
 * Time: 16:08
 */

namespace pql;

use Exception;
use Generator;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use pql\BTree\BtreePlus;
use pql\QueryRow\TableRow;
use SplFileInfo;
use SplFileObject;

/**
 * Class Table
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class Table implements ITable
{
    public const string EXT = 'pql';

    private const string INDEX_DIR = 'index';

    private const string INDEX_EXTENSION = 'index';

    public const int FIRST_LINE_LENGTH = 102400;

    public const string COLUMN_DELIMITER = ', ';

    private const string COLUMN_DATA_DELIMITER = ':';

    private string $name;

    private string $filePath;

    private string $tableDir;

    private string $indexDir;


    private string $fileName;


    private int $size;

    /**
     * @var TableRow[] $rows
     */
    private array $rows;

    /**
     * @var int $rowsCount
     */
    private int $rowsCount;

    /**
     * @var Database $database
     */
    private Database $database;

    /**
     * @var TableColumn[] $columns
     */
    private array $columns;

    /**
     * @var int $columnsCount
     */
    private int $columnsCount;
    
    /**
     * @var string $columnsString
     */
    private string $columnsString;

    /**
     * @var array $indexes
     */
    private array $indexes;

    /**
     * @var array $indexNames
     */
    private array $indexNames;

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
    public function __construct(Database $database, string $name)
    {
        $this->database = $database;
        $this->fileName = $name . '.' . self::EXT;

        $sep = DIRECTORY_SEPARATOR;

        $databaseDir = $database->getDir();

        if (!file_exists($databaseDir)) {
            $message = sprintf('Directory of database "%s" does not exist.', $database->getName());

            throw new Exception($message);
        }

        $tableDir = $databaseDir .  $name . $sep;

        if (!file_exists($tableDir)) {
            $message = sprintf('Directory "%s" of table "%s" does not exist.', $tableDir,  $name);

            throw new Exception($message);
        }

        $this->tableDir = $tableDir;

        $filePath = $tableDir . $name . '.' . self::EXT;

        if (!file_exists($filePath)) {
            throw new Exception(sprintf('File "%s" of table "%s" does not exist.', $filePath, $name));
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
            FileSystem::createDir($index_dir);
        }

        $this->indexDir = $index_dir;
        $this->indexes  = [];
        $this->indexNames = [];

        $indexes = Finder::findFiles('*.' .self::INDEX_EXTENSION)->in($this->indexDir);

        /**
         * @var SplFileInfo $index
         */
        foreach ($indexes as $indexFile) {
            $fileName = str_replace('.' . self::INDEX_EXTENSION, '', $indexFile->getFilename());

            $indexInstance = new BtreePlus($this->indexDir . $indexFile->getFilename());

            $readIndex = $indexInstance->read();

            if ($readIndex === false) {
                $indexInstance->write() ;
            }

            $this->indexes[$fileName] = $readIndex;
            $this->indexNames[$fileName] = $indexFile->getFilename();
        }

        $fileContent = file($filePath);
        $columns     = [];
        $columnNames = explode(self::COLUMN_DELIMITER, trim($fileContent[0]));

        foreach ($columnNames as $column) {
            $columnExploded = explode(self::COLUMN_DATA_DELIMITER, trim($column));
            $columns[]      = new TableColumn(
                $columnExploded[0],
                $columnExploded[1],
                $columnExploded[2] === 'unique',
                $this
            );
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
        /*else {
            $this->columnsString = substr($fileContent[0], 0, -1);
            $this->lineEnds = "\n";
        }
        */

        $this->lineEndsLength = mb_strlen($this->lineEnds);
    }

    public function getDatabase() : Database
    {
        return $this->database;
    }

    /**
     * @return int
     */
    public function getRowsCount() : int
    {
        return $this->rowsCount;
    }

    /**
     * @return TableColumn[]
     */
    public function getColumns() : array
    {
        return $this->columns;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getFileName() : string
    {
        return $this->fileName;
    }
    
    public function getColumnsString() : string
    {
        return $this->columnsString;
    }

    public function getIndexes() : array
    {
        return $this->indexes;
    }

    public function getIndexNames() : array
    {
        return $this->indexNames;
    }

    public function getFilePath() : string
    {
        return $this->filePath;
    }

    public function getTableDir() : string
    {
        return $this->tableDir;
    }

    public function getFileEnds() : string
    {
        return $this->lineEnds;
    }

    public function getFileEndsLength(): int
    {
        return $this->lineEndsLength;
    }

    public function getIndexDir(): string
    {
        return $this->indexDir;
    }

    public function getDirPath(): string
    {
        $sep = DIRECTORY_SEPARATOR;

        return $this->database->getDir() . $sep . $this->name . $sep;
    }

    public function columnExists(string $column): bool
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
    public static function create(Database $database, string $name, array $columns)
    {
        $sep = DIRECTORY_SEPARATOR;
        $databaseDir = $database->getDir();

        if (!file_exists($databaseDir)) {
            $message = sprintf('Directory of database "%s" does not exist.', $database->getName());

            throw new Exception($message);
        }

        $tableDir = $databaseDir . $name . $sep;

        if (!file_exists($tableDir)) {
            FileSystem::createDir($tableDir);
        }

        $index_dir = $tableDir . 'index' . $sep;

        if (!file_exists($index_dir)) {
            FileSystem::createDir($index_dir);
        }

        $columnsNames = [];

        /**
         * @var TableColumn $column
         */
        foreach ($columns as $column) {
            if ($column instanceof TableColumn) {
                $columnsNames[] = sprintf('%s:%s:%s',
                    $column->getName(),
                    $column->getType(),
                    $column->getUnique() ? 'unique' : 'nonunique'
                );
            } else {
                throw new Exception('Unknown param "$columns". It should be instance of TableColumn class.');
            }
        }

        $tableFile = $tableDir . $name . '.' . self::EXT;

        FileSystem::write(
            $tableFile,
            implode(self::COLUMN_DELIMITER, $columnsNames) . "\n"
        );

        return new Table($database, $name);
    }

    /**
     * @param string $name
     * @param string $type
     * @param bool $unique
     * @return Table
     * @throws Exception
     */
    public function addColumn($name, $type, $unique) : Table
    {
        if ($this->columnExists($name)) {
            $message = sprintf('Table "%s" already has column "%s".', $this->name, $name);

            throw new Exception($message);
        }

        if (!in_array($type, TableColumn::COLUMN_TYPES, true)) {
            $message = sprintf('Unknown "%s" column type.', $type);

            throw new Exception($message);
        }

        if (!is_bool($unique)) {
            $message = sprintf('$unique "%s" is not boolean.', $unique);

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
        $this->columns[] = new TableColumn($name, $type, $unique, $this);
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
    public function deleteColumn(string $name) : Table
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

        $result = file_put_contents($this->filePath, $newRowsString);

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

    public function renameColumn(string $from, string $to): Table
    {
        return $this;
    }

    public function truncate() : Table
    {
        $firstRow = $this->columnsString . $this->lineEnds;

        $file = new SplFileObject($this->filePath, 'wb');

        $file->fwrite($firstRow);
        $file = null;

        return $this;
    }

    public function delete() : bool
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
     * @param bool $returnObject
     *
     * @return Generator
     * @throws Exception
     */
    public function getRows(bool $returnObject = false): Generator
    {
        $this->rows = [];

        $streamer = new SplFileObject($this->filePath, 'r');

        $columnNames = [];

        foreach ($this->columns as $column) {
            $columnNames[] = $column->getName();
        }
        
        while (!$streamer->eof()) {
            $row = $streamer->fgets();

            if (!$row) {
                continue;
            }

            $row = str_replace($this->lineEnds, '', $row);
            $exploded = explode(self::COLUMN_DELIMITER, $row);

            if (count($exploded) !== count($columnNames)) {
                continue;
            }

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
                        $columnValuesArray[$column->getName()] = (string) $columnValuesArray[$column->getName()];
                        break;
                    default:
                        $message = sprintf('Unknown column type "%s".', $column->getType());

                        throw new Exception($message);
                }
            }

            if ($returnObject) {
                yield new TableRow($columnValuesArray);
            } else {
                yield $columnValuesArray;
            }
        }
    }

    public function toArray() : array|false
    {
        return file($this->filePath);
    }
}
