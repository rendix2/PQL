<?php

namespace pql\QueryExecutor\Storage;

/**
 * Interface IStorage
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecutor\Storage
 */
interface IStorage
{

    public function create();

    public function read();

    public function update();

    public function delete();
}