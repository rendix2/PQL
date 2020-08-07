<?php

namespace pql\QueryExecutor\Storage;

/**
 * Class StandardStorage
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecutor\Storage
 */
class StandardStorage implements IStorage
{

    public function create()
    {
        throw new \Exception();
    }

    public function read()
    {
        throw new \Exception();
    }

    public function update()
    {
        throw new \Exception();
    }

    public function delete()
    {
        throw new \Exception();
    }
}