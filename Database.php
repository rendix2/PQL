<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 30. 1. 2019
 * Time: 16:09
 */

/**
 * Class Database
 *
 * @author Tomáš Babický tomas.babicky@websta.de
 */
class Database
{
    const DATABASE_DIR = '%s/data/%s';

    private $name;

    private $size;

    private $tablesCount;

    /**
     * Database constructor.
     */
    public function __construct($name)
    {
        if (!is_dir(self::getPath($name))) {
            throw new Exception('Database does not exist.');
        }

        $mask = sprintf('*.%s', Table::EXT);
        $size = 0;

        foreach (\Nette\Utils\Finder::findFiles($mask) as $file) {
            $size += $file->getSize();
        }

        $this->size        = $size;
        $this->name        = $name;
        $this->tablesCount = \Nette\Utils\Finder::findFiles($mask)->count();
    }

    /**
     * Database destructor.
     */
    public function __destruct()
    {
        $this->name = null;
    }

    public static function getPath($name)
    {
        return sprintf(self::DATABASE_DIR,__DIR__, $name);
    }

    /**
     * @return Table[]
     */
    public function getTables()
    {
        $mask   = sprintf('*.%s', Table::EXT);
        $tables = [];

        foreach (\Nette\Utils\Finder::findFiles($mask) as $file) {
            $tables[] = new Table($file->getBaseName());
        }

        return $tables;
    }

    public static function create($name)
    {
        $path = self::getPath($name);

        if (is_dir($path)) {
            throw new Exception('Database already exists.');
        } else {
            if (!mkdir($path)) {
                throw new Exception('Database was not created.');
            }
        }

        return new Database($name);
    }

    public function delete()
    {
    }
}
