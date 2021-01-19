<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Node.php
 * User: Tomáš Babický
 * Date: 18.01.2021
 * Time: 17:47
 */

namespace pql\BTree;

/**
 * Class Node
 *
 * @package pql\BTree
 */
class Node
{
    /**
     * @var array $key
     */
    public $key;

    /**
     * @var array $ptr
     */
    public $ptr;

    /**
     * @var int $size
     */
    public $size;

    /**
     * @var bool $isLeaf
     */
    public $isLeaf;

    /**
     * Node constructor.
     */
    public function __construct()
    {
        $this->key = array_fill(0, Tree::MAX, null);
        $this->ptr = array_fill(0, Tree::MAX+1, null);
        $this->size = 0;
    }
}
