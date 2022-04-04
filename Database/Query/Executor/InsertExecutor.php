<?php
/**
 *
 * Created by PhpStorm.
 * Filename: InsertExecutor.php
 * User: Tomáš Babický
 * Date: 17.09.2021
 * Time: 22:47
 */

namespace PQL\Database\Query\Executor;

use Exception;
use PQL\Database\Storage\IStorage;
use PQL\Database\Storage\StandardStorage;
use PQL\Database\Query\Builder\InsertBuilder;

/**
 * Class InsertExecutor
 *
 * @package PQL\Database\Query\Executor
 */
class InsertExecutor
{
    /**
     * @var InsertBuilder $query
     */
    private InsertBuilder $query;

    /**
     * @var IStorage $storage
     */
    private IStorage $storage;

    /**
     * @param InsertBuilder $query
     */
    public function __construct(InsertBuilder $query)
    {
        $this->query = $query;
        $this->storage = new StandardStorage($query->getTable()->getTable());
    }

    /**
     * @throws Exception
     */
    private function checks() : void
    {
        $table = $this->query->getTable()->getTable();
        $row = $this->query->getData();

        foreach ($row as $insertColumn => $insertValue) {
            $found = false;

            foreach ($table->getColumns() as $tableColumn) {
                if ($insertColumn === $tableColumn->name)  {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $message = sprintf('Column "%s" was not found in inserter table.', $insertColumn);

                throw new Exception($message);
            }
        }
    }

    public function run() : bool
    {
        $this->checks();

        $row = $this->query->getData();

        return $this->storage->add($row);
    }
}