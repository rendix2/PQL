<?php
/**
 *
 * Created by PhpStorm.
 * Filename: EntityGenerator.php
 * User: Tomáš Babický
 * Date: 28.08.2021
 * Time: 5:23
 */

namespace PQL;


use Nette\PhpGenerator\PhpFile;
use Nette\Utils\FileSystem;
use stdClass;

class EntityGenerator
{

    private Table $table;

    /**
     * EntityGenerator constructor.
     *
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    public function run()
    {
        $phpFile = new PhpFile();
        $namespace = $phpFile->addNamespace('PQL\\Database\\Entity');
        $class = $namespace->addClass($this->table->getName());
        $class->addExtend(stdClass::class);

/*        foreach ($this->table->getColumns() as $key => $column) {
            $property = new Property($column->tableName);
            $property->setPublic();
            $property->setType($column->type);
            $property->setNullable();

            $class->addMember($property);
        }*/

        $sep = DIRECTORY_SEPARATOR;

        FileSystem::write(__DIR__ . $sep . '..'. $sep .'temp' . $sep . 'Entity' . $sep . $this->table->getName() . '.php', $phpFile);
    }


}