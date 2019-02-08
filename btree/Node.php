<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 7. 2. 2019
 * Time: 17:51
 */

namespace BTree;

/**
 * Class Node
 *
 * @author Tomáš Babický tomas.babicky@websta.de
 */
class Node
{
    /**
     * @var bool $leaf
     */
    public $leaf;
    
    public $keys;
    
    /**
     * 
     * @var Node[] $c
     */
    public $c;
    
    public $n;
    
    public $t;
   
   

    /**
     * Node constructor.
     *
     * @param $leaf
     */
    public function __construct($t, $leaf)
    {
        $this->leaf = $leaf;
        $this->t    = $t;
        $this->n    = 0;
        $this->keys = [];
        $this->c    = [];
    }

    /**
     * Node destructor.
     */
    public function __destruct()
    {
        $this->leaf = null;
        $this->keys = null;
        $this->c    = null;
        $this->n    = null;
        $this->t    = null;
    }
    
    public function insertNonFull(Node $x, $k)
    {
        $i = $x->n;
        
        if ($x->leaf) {
            while ($i >= 1 && $k < $x->keys[$i]) {
                $x->keys[$i + 1] = $x->keys[$i];
                $i--;
            }

            $x->keys[$i + 1] = $k;
            $x->n = $x->n + 1;
        } else {
            while ($i >= 1 && $k < $x->keys[$i]) {
                $i--;
            }
            
            $i = $i + 1;
            
            if ($x->c[$i]->n === 2 * $this->t - 1) {
                $this->splitChild($x, $i);
                
                if ($k > $x->keys[$i]) {
                    $i++;
                }
            }
            $this->insertNonFull($x->c[$i],$k);
        }
    }
    
    public function splitChild(Node $x, $i)
    {
        $z    = new Node($x->t, $i);
        $y    = $x->c[$i];        
        $z->n = $this->t - 1;
        
        for ($j = 1; $j < $this->t - 1; $j++) {
            $z->keys[$j] = $y->keys[$j + $this->t];
        }
        
        if ($y->leaf === false) {
            for ($j = 1; $j < $this->t; $j++) {
                $z->c[$j] = $z->c[$j + $this->t];
            }
        }
        
        $y->n = $this->t - 1;
        
        for ($j = $x->n + 1; $j >= $i + 1; $j++) {
            $x->c[$j + 1] = $x->c[$j];
        }
        
        $this->c[$i + 1] = $z;
        
        
        for ($j = $x->n - 1; $j >= $i; $j--) {
            $x->keys[$j + 1] = $x->keys[$j];
        }
        
        $this->keys[$i] = $y->keys[$this->t];
        $this->n        = $this->n + 1;
    }

    public static function create()
    {

    }
}
