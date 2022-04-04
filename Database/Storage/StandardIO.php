<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IO.php
 * User: Tomáš Babický
 * Date: 21.09.2021
 * Time: 23:40
 */

namespace PQL\Database\Storage;

use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use PQL\Database\Index\BtreePlus;
use PQL\Database\Index\Node;
use PQL\Database\Table;
use stdClass;

/**
 * Class StandardIO
 *
 * @package PQL\Database\Storage
 */
class StandardIO implements  IIO
{
    /**
     * @var Table $table
     */
    private Table $table;

    /**
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function readAllTableData() : array
    {
        return Json::decode(FileSystem::read($this->table->getDataFilePath()));
    }

    public function readTableMetaData() : stdClass
    {
        return Json::decode(FileSystem::read($this->table->getMetaFilePath()));
    }

    public function readPrimaryIndex() : BtreePlus
    {
        $params = ['allowed_classes' => [BtreePlus::class, Node::class, stdClass::class]];

        return unserialize(FileSystem::read($this->table->getPrimaryIndexFilePath()), $params);
    }

    public function writeIntoTableDate(array $rows) : bool
    {
        $jsonRows = Json::encode($rows, Json::PRETTY);

        try {
            FileSystem::write($this->table->getDataFilePath(), $jsonRows);

            return true;
        } catch (IOException $e) {
            return false;
        }
    }

    public function writeIntoTableMetaData(stdClass $metaData) : bool
    {
        try {
            FileSystem::write($this->table->getMetaFilePath(), Json::encode($metaData, Json::PRETTY));

            return true;
        } catch (IOException $e) {
            return false;
        }
    }

    public function writeIntoPrimaryIndex(BtreePlus $btreePlus) : bool
    {
        try {
            FileSystem::write($this->table->getPrimaryIndexFilePath(), serialize($btreePlus));

            return true;
        } catch (IOException $e) {
            return false;
        }
    }
}