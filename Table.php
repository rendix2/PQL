<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 30. 1. 2019
 * Time: 16:08
 */

/**
 * Class Table
 *
 * @author Tomáš Babický tomas.babicky@websta.de
 */
class Table
{
    const EXT = 'pql';

    private $name;

    private $fileName;

    private $size;

    private $rows;

    /**
     * Table constructor.
     */
    public function __construct($name)
    {
        $tableFileName = self::getPath($name);

        if (!is_file($tableFileName)) {
            throw new Exception('Table does not exist.');
        }

        $fileSize = filesize($tableFileName);

        if ($fileSize === false) {
            throw new Exception('There was problem during counting table size.');
        }

        $this->name     = $name;
        $this->fileName = $tableFileName;
        $this->size     = $fileSize;
    }

    /**
     * Table destructor.
     */
    public function __destruct()
    {
        $this->rows = null;
    }

    public static function getPath($name)
    {
        return sprintf('%s/%s.%s',  Database::DATABASE_DIR, $name, self::EXT);
    }

    public function getRows()
    {

    }
}
