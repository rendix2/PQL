<?php
/**
 *
 * Created by PhpStorm.
 * Filename: TemporaryTable.php
 * User: Tomáš Babický
 * Date: 31.08.2021
 * Time: 16:17
 */

namespace PQL;


use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Random;

class TemporaryTable implements ITable
{

    private string $name;

    private string $path;

    /**
     * TemporaryTable constructor.
     *
     * @param array  $data
     */
    public function __construct(array $data)
    {
        $sep = DIRECTORY_SEPARATOR;

        $this->path = __DIR__ . $sep .'..' . $sep . 'temp' . $sep . 'Tables' . $sep . Random::generate() . '.json';

        FileSystem::write($this->path, Json::encode($data));
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }

        FileSystem::delete($this->path);
    }

    public function getData()
    {
        $tableJson = FileSystem::read($this->path);

        return Json::decode($tableJson);
    }
}