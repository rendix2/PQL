<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Node.php
 * User: Tomáš Babický
 * Date: 18.09.2021
 * Time: 15:14
 */

namespace PQL\Database\Index;

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
    public array $key;

    /**
     * @var array $ptr
     */
    public array $ptr;

    /**
     * @var int $size
     */
    public int $size;

    /**
     * @var bool $isLeaf
     */
    public bool $isLeaf;

    /**
     * Node constructor.
     */
    public function __construct()
    {
        $this->key = array_fill(0, BtreePlus::MAX, null);
        $this->ptr = array_fill(0, BtreePlus::MAX+1, null);
        $this->size = 0;
    }
}