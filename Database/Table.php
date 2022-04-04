<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Table.php
 * User: Tomáš Babický
 * Date: 26.08.2021
 * Time: 22:31
 */

namespace PQL\Database;


use Exception;
use PQL\Database\Index\BtreePlus;
use PQL\Database\Query\Builder\Expressions\Column;
use PQL\Database\Storage\IStorage;
use PQL\Database\Storage\StandardStorage;
use stdClass;

/**
 * Class Table
 *
 * @package PQL\Database
 */
class Table implements ITable
{
    private const META_FILE_NAME = 'meta.json';

    private const DATA_FILE_NAME = 'data.json';

    private const PRIMARY_INDEX_FILE = 'PRIMARY.index';

    /**
     * @var string $metaFilePath
     */
    private string $metaFilePath;

    /**
     * @var string $dataFilePath
     */
    private string $dataFilePath;

    /**
     * @var Database $database
     */
    private Database $database;

    /**
     * @var stdClass $metaData
     */
    private stdClass $metaData;

    /**
     * @var string $name
     */
    private string $name;

    /**
     * @var string $dir
     */
    private string $dir;

    /**
     * @var array $columns
     */
    private array $columns;

    /**
     * @var array $columnNames
     */
    private array $columnNames;

    /**
     * @var stdClass[] $data
     */
    private array $data;

    /**
     * @var BtreePlus $primaryIndex
     */
    private BtreePlus $primaryIndex;

    /**
     * @var string $primaryIndexFilePath
     */
    private string $primaryIndexFilePath;

    /**
     * @var IStorage $storage
     */
    private IStorage $storage;

    /**
     * Table constructor.
     *
     * @param Database $database
     * @param string   $name
     * @param string   $dir
     *
     * @throws Exception
     */
    public function __construct(
        Database $database,
        string $name,
        string $dir
    ) {
        $this->database = $database;
        $this->name = $name;
        $this->dir = $dir;

        $sep = DIRECTORY_SEPARATOR;

        $metaFilePath = $dir . $sep . static::META_FILE_NAME;
        $dataFilePath = $dir . $sep . static::DATA_FILE_NAME;
        $primaryIndexFilePath = $dir . $sep . static::PRIMARY_INDEX_FILE;

        $this->metaFilePath = $metaFilePath;
        $this->dataFilePath = $dataFilePath;
        $this->primaryIndexFilePath = $primaryIndexFilePath;

        $this->storage = new StandardStorage($this);

        if (file_exists($metaFilePath)) {
            $metaData = $this->storage->readTableMetaData();

            $this->metaData = $metaData;
            $this->columns = $metaData->columns;
        } else {
            $message = sprintf('Column file of table %s does not exist.', $name);

            throw new Exception($message);
        }

        if (!file_exists($dataFilePath)) {
            $message = sprintf('Data file of table %s does not exist.', $name);

            throw new Exception($message);
        }

        if (file_exists($primaryIndexFilePath)) {
            $primaryIndex = $this->storage->readPrimaryIndex();
        } else {
            $primaryIndex = new BtreePlus();
        }

        $this->primaryIndex = $primaryIndex;

        //$this->data = $this->getAllData();

        /*
        $this->data = $this->getAllData();

        foreach ($this->getAllData() as $row) {
            $primaryIndex->insert($row);
        }

        $w = new StandardIO($this);
        $w->writeIntoPrimaryIndex($primaryIndex);
        */
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function getAllData() : array
    {
        return $this->storage->getAllTableData();
    }

    /**
     * @return array
     */
    public function getColumnNames() : array
    {
        return $this->columnNames;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getColumns() : array
    {
        return $this->columns;
    }

    /**
     * @return string
     */
    public function getDataFilePath() : string
    {
        return $this->dataFilePath;
    }

    public function getMetaFilePath() : string
    {
        return $this->metaFilePath;
    }

    /**
     * @return BtreePlus
     */
    public function getPrimaryIndex() : BtreePlus
    {
        return $this->primaryIndex;
    }

    /**
     * @return stdClass
     */
    public function getMetaData() : stdClass
    {
        return $this->metaData;
    }

    /**
     * @return string
     */
    public function getPrimaryIndexFilePath() : string
    {
        return $this->primaryIndexFilePath;
    }

    public function checkColumnExists(Column $column)
    {
        foreach ($this->columns as $tableColumn) {
            if ($tableColumn->name === $column->getName()) {
                return true;
            }
        }

        return false;
    }
}