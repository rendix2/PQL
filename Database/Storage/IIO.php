<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IIo.php
 * User: Tomáš Babický
 * Date: 21.09.2021
 * Time: 23:46
 */

namespace PQL\Database\Storage;

use PQL\Database\Index\BtreePlus;
use stdClass;

/**
 * Interface IIO
 *
 * @package PQL\Database\Storage
 */
interface IIO
{
    public function readAllTableData() : array;

    public function readTableMetaData() : stdClass;

    public function readPrimaryIndex() : BtreePlus;

    public function writeIntoTableDate(array $rows) : bool;

    public function writeIntoTableMetaData(stdClass $metaData) : bool;

    public function writeIntoPrimaryIndex(BtreePlus $btreePlus) : bool;
}