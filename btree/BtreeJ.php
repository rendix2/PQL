<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 28. 2. 2019
 * Time: 13:11
 */

namespace BTree;

/**
 * Class BtreeJ
 *
 * @package BTree
 * @author  rendix2
 */
class BtreeJ
{
    /**
     * is leaf?
     *
     * @var bool $leaf
     */
    public $leaf;

    /**
     * how much keys is stored in node
     *
     * @var int $n
     */
    public $n;

    /**
     * stored data in node
     *
     * @var array $keys
     */
    public $keys;

    /**
     * how much keys we can store in node
     *
     * @var int $degree
     */
    public $degree;

    /**
     * children of node
     *
     * @var array $c
     */
    public $c;

    /**
     * root node
     *
     * @var BtreeJ $root
     */
    public $root;

    /**
     * BtreeJ constructor.
     */
    public function __construct()
    {
        $this->leaf   = true;
        $this->degree = 5;
        $this->n      = 0;
        $this->keys   = [];
        $this->c      = [];
        $this->root   = $this;
    }

    /**
     * BtreeJ destructor.
     */
    public function __destruct()
    {
        $this->leaf   = null;
        $this->n      = null;
        $this->keys   = null;
        $this->degree = null;
        $this->c      = null;
        $this->root   = null;
    }
    
    public function write($path)
    {
        file_put_contents($path, serialize($this));
    }
    
    /**
     * 
     * @param string $path
     * @return BtreeJ
     */
    public static function read($path)
    {
        return unserialize(file_get_contents($path));
    }

    /**
     * @param BtreeJ $node
     */
    private function dispatch(BtreeJ $node)
    {
        if ($node === $this->root) {
            $this->root = $node;
        }
    }

    /**
     * @param BtreeJ $T
     */
    public function create(BtreeJ $T)
    {
        $y = new BtreeJ();

        $y->leaf = true;
        $y->n    = 0;
        $T->root = $y;
    }

    /**
     * @param BtreeJ $x
     * @param int    $i
     * @param BtreeJ $y
     */
    private function split(BtreeJ $x, $i, BtreeJ $y)
    {
        $z = new BtreeJ();

        $z->leaf = $y->leaf;
        $z->n    = $this->degree - 1;

        for ($j = 0; $j < $this->degree - 1; $j++) {
            $z->keys[$j] = $y->keys[$j + $this->degree];
        }

        if (!$y->leaf) {
            for ($j = 0; $j < $this->degree; $j++) {
                $z->c[$j] = $y->c[$j + $this->degree];
            }
        }

        $y->n = $this->degree - 1;

        for ($j = $x->n; $j >= $i + 1; $j--) {
            $x->c[$j + 1] = $x->c[$j];
        }

        $x->c[$i + 1] = $z;

        for ($j = $x->n - 1; $j >= $i; $j--) {
            $x->keys[$j + 1] = $x->keys[$j];
        }

        $x->keys[$i] = $y->keys[$this->degree - 1];
        $x->n        = $x->n + 1;

        $this->dispatch($y);
        $this->dispatch($z);
        $this->dispatch($x);
    }

    /**
     * @param BtreeJ $x
     * @param mixed  $k
     */
    private function insertNonFull(BtreeJ $x, $k)
    {
        $i = $x->n - 1;

        if ($x->leaf) {
            for (;$i >= 0 && strcmp($k, $x->keys[$i]) < 0; $i--) {
                $x->keys[$i + 1] = $x->keys[$i];
            }

            $x->keys[$i + 1] = $k;
            $x->n            = $x->n + 1;

            $this->dispatch($x);
        } else {
            /*
           for (; $i >-1; $i--) {
               if (strcmp($k, $x->keys[$i]) >= 0) {
                   break;
               }

               $i++;
           }
            */
            $i++;

           if ($x->c[$i] !== null && $x->c[$i]->n === 2 * $this->degree - 1) {
               $this->split($x, $i, $x->c[$i]);

               if (strcmp($k, $x->keys[$i]) > 0) {
                   $i++;
               }
           }

            $this->insertNonFull($x->c[$i], $k);
        }
    }

    /**
     * @param mixed $k
     *
     * @return BtreeJ
     */
    public function insert($k)
    {
        $r = $this->root;

        if ($r->n === 2 * $this->degree - 1) {
            $this->root = new BtreeJ();

            $this->root->leaf = false;
            $this->root->n    = 0;

            $this->dispatch($this->root);

            $this->split($this->root, 0, $r);
            $this->insertNonFull($this->root, $k);
        } else {
            $this->insertNonFull($r, $k);
        }

        return $r;
    }

    /**
     * @param BtreeJ $x
     * @param mixed  $k
     *
     * @return BtreeJ|null
     */
    public function search(BtreeJ $x, $k)
    {
        $i = 0;

        while ($i < $x->n && strcmp($k, $x->keys[$i]) > 0){
            $i++;
        }

        if ($i <= $x->n && $k === $x->keys[$i]) {
            return $x;
        } elseif($x->leaf) {
            return null;
        } else {
            return $this->search($x->c[$i], $k);
        }
    }
}
