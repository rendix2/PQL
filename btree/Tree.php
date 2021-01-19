<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Tree.php
 * User: Tomáš Babický
 * Date: 18.01.2021
 * Time: 17:47
 */

namespace pql\BTree;

/**
 * Class Tree
 *
 * @package pql\BTree
 */
class Tree
{
    const MAX = 3;

    /**
     * @var Node|null $root
     */
    public $root;

    /**
     * Tree constructor.
     */
    public function __construct()
    {
        $this->root = null;
    }

    /**
     * @param $x
     */
    public function insert($x)
    {
        if ($this->root === null) {
            $root = new Node();
            $root->key[0] = $x;
            $root->isLeaf = true;
            $root->size = 1;

            $this->root = $root;
        } else {
            $cursor = $this->root;
            $parent = null;

            while ($cursor->isLeaf === false) {
                $parent = $cursor;

                for ($i = 0; $i < $cursor->size; $i++) {
                    if ($x < $cursor->key[$i]) {
                        $cursor = $cursor->ptr[$i];

                        break;
                    }

                    if ($i === $cursor->size - 1) {
                        $cursor = $cursor->ptr[$i + 1];

                        break;
                    }
                }
            }

            if ($cursor->size < self::MAX) {
                $i = 0;

                while ($x > $cursor->key[$i] && $i < $cursor->size) { // while ($x > $cursor->key[$i] && $i < $cursor->size ) {
                    $i++;
                }

                for ($j = $cursor->size; $j > $i; $j--) {
                    $cursor->key[$j] = $cursor->key[$j - 1];
                }

                $cursor->key[$i] = $x;
                $cursor->size++;

                $cursor->ptr[$cursor->size] = $cursor->ptr[$cursor->size - 1];
                $cursor->ptr[$cursor->size - 1] = null;
            } else {
                $newLeaf = new Node();
                $virtualNode = array_fill(0, self::MAX + 1, null);

                for ($i = 0; $i < self::MAX; $i++) {
                    $virtualNode[$i] = $cursor->key[$i];
                }

                $i = 0;

                while ($x > $virtualNode[$i] && $i < self::MAX) {
                    $i++;
                }

                for ($j = self::MAX + 1; $j > $i; $j--) {
                    $virtualNode[$j] = $virtualNode[$j - 1];
                }

                $virtualNode[$i] = $x;
                $newLeaf->isLeaf = true;

                $cursor->size = (self::MAX + 1) / 2;
                $newLeaf->size = self::MAX + 1 - (self::MAX + 1) / 2;

                $cursor->ptr[$cursor->size] = $newLeaf;
                $newLeaf->ptr[$newLeaf->size] = $cursor->ptr[self::MAX];

                $cursor->ptr[self::MAX] = null;

                for ($i = 0; $i < $cursor->size; $i++) {
                    $cursor->key[$i] = $virtualNode[$i];
                }

                for ($i = 0, $j = $cursor->size; $i < $newLeaf->size; $i++, $j++) {
                    $newLeaf->key[$i] = $virtualNode[$j];
                }

                if ($cursor === $this->root) {
                    $newRoot = new Node();

                    $newRoot->key[0] = $newLeaf->key[0];
                    $newRoot->ptr[0] = $cursor;
                    $newRoot->ptr[1] = $newLeaf;
                    $newRoot->isLeaf = false;
                    $newRoot->size = 1;

                    $this->root = $newRoot;
                } else {
                    $this->insertInternal($newLeaf->key[0], $parent, $newLeaf);
                }
            }
        }
    }

    public function insertInternal($x, Node $cursor, Node $child)
    {
        if ($cursor->size < self::MAX) {
            $i = 0;

            while ($x > $cursor->key[$i] && $i < $cursor->size) {
                $i++;
            }

            for ($j = $cursor->size + 1; $j > $i + 1; $j--) {
                $cursor->ptr[$j] = $cursor->ptr[$j - 1];
            }

            $cursor->key[$i] = $x;
            $cursor->size++;
            $cursor->ptr[$i + 1] = $child;
        } else {
            $newInternal = new Node();
            $virtualKey = array_fill(0, self::MAX + 1, null);
            $virtualPtr = array_fill(0, self::MAX + 2, null);

            for ($i = 0; $i < self::MAX; $i++) {
                $virtualKey[$i] = $cursor->key[$i];
            }

            for ($i = 0; $i < self::MAX + 1; $i++) {
                $virtualPtr[$i] = $cursor->ptr[$i];
            }

            $i = $j = 0;

            while ($x > $virtualKey[$i] && $i < self::MAX) {
                $i++;
            }

            for ($j = self::MAX + 1; $j > $i; $j--) {
                $virtualKey[$j] = $virtualKey[$j - 1];
            }

            $virtualKey[$i] = $x;

            for ($j = self::MAX + 2; $j > $i + 1; $j--) {
                $virtualPtr[$j] = $virtualPtr[$j - 1];
            }

            $virtualPtr[$i + 1] = $child;
            $newInternal->isLeaf = false;

            $cursor->size = (self::MAX + 1) / 2;
            $newInternal->size = self::MAX - (self::MAX + 1) / 2;

            for ($i = 0, $j = $cursor->size + 1; $i < $newInternal->size; $i++, $j++) {
                $newInternal->key[$i] = $virtualKey[$j];
            }

            for ($i = 0, $j = $cursor->size + 1; $i < $newInternal->size + 1; $i++, $j++) {
                $newInternal->ptr[$i] = $virtualPtr[$j];
            }

            if ($cursor === $this->root) {
                $newRoot = new Node();
                $newRoot->key[0] = $cursor->key[$cursor->size];

                $newRoot->ptr[0] = $cursor;
                $newRoot->ptr[1] = $newInternal;
                $newRoot->isLeaf = false;
                $newRoot->size = 1;

                $this->root = $newRoot;
            } else {
                $this->insertInternal($cursor->key[$cursor->size], $this->findParent($this->root, $cursor), $newInternal);
            }
        }
    }

    public function findParent(Node $cursor, Node $child)
    {
        $parent = null;

        if ($cursor->isLeaf || $cursor->ptr[0]->isLeaf) {
            return null;
        }

        for ($i = 0; $i < $cursor->size + 1; $i++) {
            if ($cursor->ptr[$i] === $child) {
                $parent = $cursor;

                return $parent;
            } else {
                $parent = $this->findParent($cursor->ptr[$i], $child);

                if ($parent !== null) {
                    return $parent;
                }
            }
        }

        return $parent;
    }

    public function search($x)
    {
        if ($this->root === null) {
            return null;
        } else {
            $cursor = $this->root;

            while ($cursor->isLeaf === false) {
                for ($i = 0; $i < $cursor->size; $i++) {
                    if ($x < $cursor->key[$i]) {
                        $cursor = $cursor->ptr[$i];

                        break;
                    }

                    if ($i === $cursor->size - 1) {
                        $cursor = $cursor->ptr[$i + 1];


                        break;
                    }
                }
            }

            for ($i = 0; $i < $cursor->size; $i++) {
                if ($cursor->key[$i] === $x) {
                    return $x;
                }
            }

            return false;
        }
    }
}