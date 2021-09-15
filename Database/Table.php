<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Table.php
 * User: Tomáš Babický
 * Date: 26.08.2021
 * Time: 22:31
 */

namespace PQL;


use Exception;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use stdClass;

class Table implements ITable
{
    private const COLUMN_FILE_NAME = 'columns.json';

    private const DATA_FILE_NAME = 'data.json';

    private Database $database;

    private string $name;

    private string $dir;

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

        $columnsFilePath = $dir . $sep . static::COLUMN_FILE_NAME;
        $dataFilePath = $dir . $sep . static::DATA_FILE_NAME;

        if (file_exists($columnsFilePath)) {
            $columnsFileContent = FileSystem::read($columnsFilePath);
            $columns = Json::decode($columnsFileContent);

            $this->columns = $columns;

            $columnNames = [];

            foreach ($columns as $column) {
                $columnNames[] = $column->name;
            }

            $this->columnNames = $columnNames;
        } else {
            $message = sprintf('Column file of table %s does not exist.', $name);

            throw new Exception($message);
        }

        if (!file_exists($dataFilePath)) {
            $message = sprintf('Data file of table %s does not exist.', $name);

            throw new Exception($message);
        }

        //$this->data = $this->getAllData();
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function getAllData() : array
    {
        $sep = DIRECTORY_SEPARATOR;

        $data = FileSystem::read($this->dir . $sep . static::DATA_FILE_NAME);

        $stdClassResult = Json::decode($data);

        $rows = [];

        foreach ($stdClassResult as $row) {
            $entity = new stdClass();

            foreach ($this->columns as $column) {
                $entity->{$column->tableName} = $row->{$column->name};
            }

            $rows[] = $entity;
        }

        return $this->data = $rows;
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
}