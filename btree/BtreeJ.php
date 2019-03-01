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
 * @author  Tomáš Babický tomas.babicky@websta.de
 */
class BtreeJ
{
    public static $cache;

    public $leaf;

    public $n;

    public $keys;

    public $degree;

    public $c;

    /**
     * @var BtreeJ $root
     */
    public $root;

    /**
     * BtreeJ constructor.
     */
    public function __construct()
    {
        $this->leaf = true;
        $this->keys = [];
        $this->c = [];
        $this->degree = 5;
        $this->root = $this;
        $this->n = 0;
    }

    /**
     * BtreeJ destructor.
     */
    public function __destruct()
    {
    }

    public function dispatch(BtreeJ $node)
    {
        if ($node === $this->root) {
            $this->root = $node;
        }

        self::$cache[] = $node;
    }

    public function split(BtreeJ $x, $i, BtreeJ $y)
    {
        $z = new BtreeJ();
        $z->leaf = $y->leaf;
        $z->n = $this->degree -1;

        for ($j = 0; $j < $this->degree -1; $j++) {
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
            $x->keys[$j+1] = $x->keys[$j];
        }

        $x->keys[$i] = $y->keys[$this->degree -1];
        $x->n = $x->n + 1;


        $this->dispatch($y);
        $this->dispatch($z);
        $this->dispatch($x);

        //bdump($z, '$z');
        //bdump($y, '$y');
        //bdump($x, '$x');
    }

    public function insertNonFull(BtreeJ $x, $k)
    {
        $i = $x->n -1;

        if ($x->leaf) {
            for (;$i >= 0 && strcmp($k, $x->keys[$i]) < 0; $i--) {
                $x->keys[$i+1] = $x->keys[$i];
            }

            $x->keys[$i + 1] = $k;

            $x->n = $x->n + 1;

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

           if ($x->c[$i] !== null && $x->c[$i]->n === 2 * $this->degree -1) {
               $this->split($x, $i, $x->c[$i]);

               if (strcmp($k, $x->keys[$i]) > 0) {
                   $i++;
               }

           }

            $this->insertNonFull($x->c[$i], $k);
        }
    }

    public function insert($k)
    {
        $r = $this->root;

        //\Tracy\Debugger::barDump($r, '$r');


        if ($r->n === 2 * $this->degree - 1) {

            $this->root = new BtreeJ();
            $this->root->leaf = false;
            $this->root->n = 0;
            //$this->root->c[0] = $r;

            $this->dispatch($this->root);

            $this->split($this->root, 0, $r);
            $this->insertNonFull($this->root, $k);
        } else {
            $this->insertNonFull($r, $k);
        }
    }
}
