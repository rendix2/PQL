<?php
/**
 *
 * Created by PhpStorm.
 * Filename: StandardStorage.php
 * User: Tomáš Babický
 * Date: 17.09.2021
 * Time: 23:01
 */

namespace PQL\Database\Storage;

use Nette\Utils\FileSystem;
use PQL\Database\Index\BtreePlus;
use PQL\Database\Table;
use stdClass;

/**
 * Class StandardStorage
 *
 * @package PQL\Database\Storage
 */
class StandardStorage implements IStorage
{
    /**
     * @var Table $table
     */
    private Table $table;

    /**
     * @var IIO $IO
     */
    private IIO $IO;

    /**
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->IO = new StandardIO($table);
    }

    /**
     * @return array
     */
    public function getAllTableData() : array
    {
        $stdClassResult = $this->IO->readAllTableData();

        $rows = [];

        foreach ($stdClassResult as $row) {
            $entity = new stdClass();

            foreach ($this->table->getColumns() as $column) {
                $entity->{$column->tableName} =
                    $row->{$column->name};
            }

            $rows[] = $entity;
        }

        return $rows;
    }

    public function readTableMetaData() : stdClass
    {
        return $this->IO->readTableMetaData();
    }

    public function readPrimaryIndex() : BtreePlus
    {
        return $this->IO->readPrimaryIndex();
    }

    public function readByPrimaryKey($id)
    {
        return $this->table->getPrimaryIndex()->searchKey($this->table->getMetaData()->primaryKey, $id);
    }

    public function writeRowsIntoTable(array $rows) : bool
    {
        $resultRows = [];

        foreach ($rows as $row) {
            $entity = new stdClass();

            foreach ($this->table->getColumns() as $column) {
                $entity->{$column->name} = $row->{$column->tableName};
            }

            $resultRows[] = $entity;
        }

        return $this->IO->writeIntoTableDate($resultRows);
    }

    public function update(array $rows) : bool
    {
        $this->recreatePrimaryIndex();

        return $this->writeRowsIntoTable($rows);
    }

    public function delete(array $rows, array $deletedRows) : bool
    {
        // decrease stats
        $metaData = $this->table->getMetaData();
        $metaData->rowsCount -= count($deletedRows);

        /*$primaryIndex = $this->table->getPrimaryIndex();

        foreach ($deletedRows as $deletedRow) {
            $primaryIndex->delete($deletedRow);
        }*/

        $this->IO->writeIntoTableMetaData($metaData);
        $this->recreatePrimaryIndex();
        //$this->IO->writeIntoPrimaryIndex($primaryIndex);

        return $this->writeRowsIntoTable($rows);
    }

    public function add(array $row) : bool
    {
        // increment stats
        $metaData = $this->table->getMetaData();
        $metaData->lastId++;
        $metaData->rowsCount++;

        // put new ID
        $row[$metaData->primaryKey] = $metaData->lastId;

        // insert into INDEX
        $primaryIndex = $this->table->getPrimaryIndex();
        $primaryIndex->insert($row);

        // add new row
        $rows = $this->getAllTableData();
        $rows[] = $row;

        // write it
        $this->IO->writeIntoTableMetaData($metaData);
        $this->IO->writeIntoPrimaryIndex($primaryIndex);
        return $this->IO->writeIntoTableDate($rows);
    }

    public function recreatePrimaryIndex() : bool
    {
        FileSystem::delete($this->table->getPrimaryIndexFilePath());

        $rows = $this->getAllTableData();

        $tree = new BtreePlus();

        foreach ($rows as $row) {
            $tree->insert($row);
        }

        return $this->IO->writeIntoPrimaryIndex($tree);
    }
}