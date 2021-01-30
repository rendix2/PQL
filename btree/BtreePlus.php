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
class BtreePlus
{
    const MAX = 3;

    /**
     * @var Node|null $root
     */
    public $root;

    /**
     * @var string $path
     */
    private $path;

    /**
     * Tree constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->root = null;
        $this->path = $path;
    }

    public function write()
    {
        file_put_contents($this->path, serialize($this));
    }

    /**
     *
     * @return BtreeJ
     */
    public function read()
    {
        return unserialize(file_get_contents($this->path));
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
                    if ($x < $cursor->key[$i]['value']) {
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

    private function insertInternal($x, Node $cursor, Node $child)
    {
        if ($cursor->size < self::MAX) {
            $i = 0;

            while ($x > $cursor->key[$i]['value'] && $i < $cursor->size) {
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

            while ($x > $virtualKey[$i]['value'] && $i < self::MAX) {
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
                    if ($x < $cursor->key[$i]['value']) {
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
                if ($cursor->key[$i]['value'] === $x) {
                    return $cursor->key[$i];
                }
            }

            return false;
        }
    }

    public function searchNode($x)
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
                if ($cursor->key[$i]['value'] === $x) {
                    return $cursor;
                }
            }

            return false;
        }
    }

    public function searchPointer($x)
    {
        if ($this->root === null) {
            return null;
        } else {
            $cursor = $this->root;

            while ($cursor->isLeaf === false) {
                for ($i = 0; $i < $cursor->size; $i++) {
                    if ($x < $cursor->key[$i]['value']) {
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
                if ($cursor->key[$i]['value'] === $x) {
                    return $cursor->ptr[$i];
                }
            }

            return false;
        }
    }

    public function delete($x)
    {
        $keyLeaf = null;

        $keyRecord = $this->search($x);

        if ($keyRecord !== false) {
            $this->root = $this->deleteEntry($x);
        }
    }

    private function deleteEntry($x)
    {
        $minKeys = 0;
        $neighbor = null;
        $neighborIndex = null;
        $kPrimeIndex = null;
        $kPrime = null;
        $capacity = null;

        $n = $this->removeEntryFromNode($x);

        if ($n === $this->root) {
            return $this->adjustRoot($this->root);
        }

        $minKeys = $n->isLeaf ? $this->cut(self::MAX - 1) : $this->cut(self::MAX) - 1;

        if ($n->size >= $minKeys) {
            return $this->root;
        }

        $neighborIndex = $this->getNeighborIndex($n);
        $kPrimeIndex = $neighborIndex === -1 ? 0 : $neighborIndex;

        $parent = $this->findParent($this->root, $n);

        $kPrime = $this->findParent($this->root, $n)->key[$kPrimeIndex];
        $neighbor = $neighborIndex === -1 ?  $parent->ptr[1] : $parent->ptrf[$neighborIndex];
        $size = $n->isLeaf ? self::MAX : self::MAX - 1;

        if ($neighbor->size + $n->size < $size) {
            return $this->coalesceNodes($this->root, $n, $neighbor, $neighborIndex, $kPrime);
        } else {
            return $this->redistributeNodes($this->root, $n, $neighbor, $neighborIndex, $kPrimeIndex, $kPrime);
        }
    }

    private function cut($length)
    {
        if ($length % 2 === 0) {
            return $length / 2;
        } else {
            return $length / 2 + 1;
        }
    }

    private function getNeighborIndex(Node $node)
    {
        $parent = $this->findParent($this->root, $node);

        for ($i = 0; $i < $parent->size; $i++) {
            if ($parent->ptr[$i] === $node) {
                return $i - 1;
            }
        }

        return false;
    }

    private function adjustRoot(Node $root)
    {
        $newRoot = null;

        if ($root->size > 0) {
            return null;
        }

        if (!$root->isLeaf) {
            $newRoot = $root->ptr[0];
        } else {
            $newRoot = null;
        }

        return $newRoot;
    }

    private function removeEntryFromNode($x)
    {
        $node = $this->searchNode($x);

        $i = 0;

        while ($node->key[$i]['value'] !== $x) {
            $i++;
        }

        for(++$i; $i < $node->size; $i++) {
            $node->key[$i - 1] = $node->key[$i];
        }

        $numPointers = $node->isLeaf ? $node->size : $node->size+1;

        $i = 0;

        $pointer = $this->searchPointer($x);

        while ($node->ptr[$i] !== $pointer) {
            $i++;
        }

        for (++$i; $i < $numPointers; $i++) {
            $node->ptr[$i - 1] = $node->ptr[$i];
        }

        $node->size--;

        if ($node->isLeaf) {
            for ($i = $node->size; $i < self::MAX - 1; $i++) {
                $node->ptr[$i] = null;
            }
        } else {
            for ($i = $node->size + 1; $i < self::MAX; $i++) {
                $node->ptr[$i] = null;
            }
        }

        return $node;
    }

    private function coalesceNodes(Node $root, Node $n, $neighbor, $neighborIndex, $kPrime)
    {
        if ($neighborIndex === -1) {
            $tmp = $n;
            $n = $neighbor;
            $neighbor = $tmp;
        }

        $neighborInsertionIndex = $neighbor->size;

        if (!$n->isLeaf) {
            $neighbor->key[$neighborInsertionIndex] = $kPrime;
            $neighbor->size++;

            $nEnd = $n->size;

            for ($i = $neighborInsertionIndex + 1, $j = 0; $j < $nEnd; $i++, $j++) {
                $neighbor->key[$i] = $n->key[$j];
                $neighbor->ptr[$i] = $n->ptr[$j];
                $neighbor->size++;
                $n->size--;
            }

            $neighbor->ptr[$i] = $n->ptr[$j];

            for ($i = 0; $i < $neighbor->size + 1; $i++) {
                $tmp = $neighbor->ptr[$i];
            }
        } else {
            for ($i = $neighborInsertionIndex, $j = 0; $j < $n->size; $i++, $j++) {
                $neighbor->key[$i] = $n->key[$j];
                $neighbor->ptr[$i] = $n->ptr[$j];
                $neighbor->size++;
            }

            $neighbor->ptr[self::MAX - 1] = $n->ptr[self::MAX - 1];
        }

        $this->root = $this->deleteEntry($n);

        return $this->root;
    }

    private function redistributeNodes(Node $root, Node $n, Node $neighbor, $neighborIndex, $kPrimeIndex, $kPrime)
    {
        if ($neighborIndex !== -1) {
            if (!$n->isLeaf) {
                $n->ptr[$n->size+1] = $n->ptr[$n->size];
            }

            for ($i = $n->size; $i > 0; $i--) {
                $n->key[$i] = $n->key[$i-1];
                $n->ptr[$i] = $n->ptr[$i-1];
            }

            if (!$n->isLeaf) {
                $n->ptr[0] = $neighbor->ptr[$n->size];
                $neighbor->ptr[$neighbor->size] = null;
                $n->key[0] = $kPrime;
            } else {
                $n->ptr[0] = $neighbor->ptr[$neighbor->size-1];
                $neighbor->ptr[$neighbor->size-1] = null;
                $n->key[0] = $neighbor->key[$neighbor->size-1];
            }
        } else {
            if ($n->isLeaf) {
                $n->key[$n->size] = $neighbor->key[0];
                $n->ptr[$n->size] = $neighbor->ptr[0];
            } else {
                $n->key[$n->size] = $kPrime;
                $n->ptr[$n->size+1] = $neighbor->ptr[0];
            }

            for ($i = 0; $i < $neighbor->size -1; $i++) {
                $neighbor->key[$i] = $neighbor->key[$i+1];
                $neighbor->ptr[$i] = $neighbor->ptr[$i+1];
            }

            if (!$n->isLeaf) {
                $neighbor->ptr[$i] = $neighbor->ptr[$i+1];
            }
        }

        $n->size++;
        $neighbor->size--;

        return $root;
    }
}
