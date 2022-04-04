<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Server.php
 * User: Tomáš Babický
 * Date: 31.08.2021
 * Time: 13:33
 */

namespace PQL\Database;

use Exception;
use Nette\Utils\Finder;
use SplFileInfo;

/**
 * Class Server
 *
 * @package PQL\Database
 */
class Server
{
    /**
     * @var Database[] $databases
     */
    private array $databases;

    public function __construct()
    {
        $sep = DIRECTORY_SEPARATOR;
        $databaseDirectory = __DIR__ .  $sep. '..'. $sep . 'data' . $sep;

        $databaseDirectories = Finder::findDirectories('*')->in($databaseDirectory);

        /**
         * @var SplFileInfo $databaseDirectory
         */
        foreach ($databaseDirectories as $databaseDirectory) {
            $databaseName = $databaseDirectory->getFilename();

            $this->databases[$databaseName] = new Database($databaseName);
        }
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @param string $name
     *
     * @return Database
     * @throws Exception
     */
    public function getDatabase(string $name) : Database
    {
        if (isset($this->databases[$name])) {
            return $this->databases[$name];
        } else {
            $message = sprintf('Database "%s" was not found.', $name);

            throw new Exception($message);
        }
    }

    /**
     * @return Database[]
     */
    public function getDatabases(): array
    {
        return $this->databases;
    }
}
