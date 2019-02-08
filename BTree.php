<?php

class BTree
{
    
    /**
     * 
     * @var BTree $root
     */
    private $root;
    
    public $n;
    
    /**
     * 
     * @var Btree[]
     */
    public $t;
    
    public $leaf;
    
    public $c;
    
    public $key;
    
    public function __construct($i)
    {
        //$this->n = $i;
        //$this->n = $i;
       $this->root = null;
        $this->leaf = true;
        $this->n = 0;
        $this->t = 5;
        //$this->c = [];
       $this->key[] = $k;
        //$this->n = 0;
        //$this->n = 0;        
    }
    
    public function create($t) 
    {
        $y = new BTree($t);
        $y->leaf = true;
        $y->n = 1;
        $this->root = $y;
        
        return $y;
    }
    
    public function traverse(BTree $root)
    {
        $i = 0;
        
        for ($i = 0;$i < $this->n; $i++) {
            if ($this->leaf === false) {
                $this->c[$i]->traverse();
            }
            
            echo $this->key[$i];
        }
        
        if ($this->leaf === false) {
            $root->traverse($root->c[$i]);
        }
    }
    
    
    public function search(BTree $x, $k)
    {        
        $i = 0;
        
        while ($i < $x->n && $k > $x->key[$i]) {
            $i = $i + 1;
        }
        
        bdump($x, '$x222');
        
        if ($k === $x->key[$i]) {
           return $k;
        }
        
        if ($this->leaf === true) {
            return null;
        }
        
        bdump($x, '$x');
        
        return $x->search($x, $k);
        
    }
    
    public function insert($k)
    {     
        
        if ($this->root === null) {
            bdump('create new node');
            $root = new BTree($k);
            $root->key[0] = $k;
            $root->n = 1;
            
            $this->root = $root;
        } else {
        
        
        
        $r = $this->root;
        
        if ($r->n === 2 * $this->t -1) {
            bdump('create new node');
            $s = new BTree($this->t);
            
            $s->leaf = false;
            //$s->n    = 1;
            $s->c[0] = $r;
            
            $this->split($s, 0);
            $i = 0;
            if( $s->key[0] < $k) {
                $i++;
            }
            
            $s->c[$i]->insertNonFull($s, $k);
            
            $this->root =  $s;
            
        } else {            
            //$r->leaf = false;
            $r->insertNonFull($r, $k);
        }
        }
        
    }
    
    public function insertNonFull(BTree $x, $k)
    {
        $i = $x->n-1;
        
        if ($x->leaf) {
            while ($i >= 1 && $k < $x->key[$i]) {
                $x->key[$i+1] = $x->key[$i];
                $i = $i - 1;
            }
            
            $x->key[$i+1] = $k;
            $x->n = $x->n + 1;
        } else {
            while ($i >= 0 && $k < $x->key[$i]) {
                $i = $i - 1;
            }
            
            $i = $i + 1;
            
            if ($x->c[$i + 1]->n === 2 * $this->t - 1) {
                 $this->split($x, $i +1);
                
                if ($k > $x->key[$i+1]) {
                    $i = $i + 1;
                }
            }
            
            $x->c[$i+1]->insertNonFull($x->c[$i+1], $k);
        }
    }
    
    public function split(BTree $x, $i)
    {
        bdump('create new node');
        $z = new BTree($i);
        
        $y = $x->c[$i];
        $z->leaf = $y->leaf;
        
        $z->n = $this->t - 1;
                      
       for ($j = 1; $j < $this->t - 1; $j++) {
           $z->key[$j] = $y->key[$j+ $this->t];
       }
       
       if (!$y->leaf) {
           for($j = 1; $j < $this->t; $j++) {
               $z->c[$j] = $y->c[$j+$this->t];
           }
       }
       
       $y->n = $x->t - 1;
       
       for ($j = $x->n; $j > $i; $j--) {
           $x->c[$j+1] = $x->c[$j];
       }
       
       $x->c[$i+1] = $z;
       
       for($j = $x->n-1; $j > $i; $j--) {
           $x->key[$j+1] = $x->key[$j];
       }
       
       $x->key[$i] = $y->key[$x->t-1];
       
       //$x->n = $x->n + 1;
       
       //return $x;

    }
    
}

