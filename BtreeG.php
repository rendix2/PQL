<?php

class BtreeG
{
   
    public $n;
    
    public $c;
    
    public $leaf;
    
    public $t;
    
    public $key;
    
    public $root;
    
    public function __construct()
    {
        $this->leaf = false;
        $this->n = 0;
        $this->t = 5;
        $this->root = null;
        $this->c = [];
        $this->key = [];
    }
    
    public function create(BtreeG $T)
    {
        $x = new BtreeG();
        $x->root = $T;
        $x->leaf = true;
        $x->n = 0;
        $T->root = $x;
        
        return $T;
    }
    
    
    public function insert(BtreeG $T, $k) 
    {
        $r = $T->root;
        
        bdump($r, '$r');
        
        if ($r->n=== 2 * $T->t - 1) {
            $s = new BtreeG();
            
            $s->leaf = false;
            $s->n = 0;
            $s->c[0] = $r;
            
            $this->root = $s;
            $T->root = $s;
                        
            $this->split($s, 0);
            $this->insertNonFull($s, $k);                       
        } else {
            $this->insertNonFull($r, $k);
        }
    }
    
    public function insertNonFull(BtreeG $x, $k)
    {
        $i = $x->n - 1;
        
        if ($x->leaf) {  
            /*
            while ($i > 0 && $k <= $x->key[$i]) {
                $i = $i - 1;
                $x->key[$i+1] = $x->key[$i];
                
            }
            
            $x->key[$i] = $k;
            */
            
            $x->key[$i] = $k;
            
            $x->n = $x->n + 1;
            $x->root = $this->root;
        } else {
            /*
            while ($i > -1  && $k <= $x->key[$i]) {
                $i = $i + 1;
            }
            
            $i = $i + 1;
            */
            
            if ($x->c[$i]->n === 2 * $this->t -1 ) {
                $x>split($x, $i);
                
                if ($k > $x->key[$i]) {
                    $i = $i + 1;
                }
                
                return $this->insertNonFull($x->c[$i], $k);
            }
        }
    }
    
    public function split(BtreeG $x, $i)
    {
        $z = new BtreeG();
        $y = $x->c[$i];
        $z->leaf = $y->leaf;
        $z->n = $x->t - 1;
        
        for($j = 0; $j < $x->t - 1; $j++) {
            $z->key[$j] = $y->key[$j + $x->t];
        }
        
        if (!$y->leaf) {
            for ($j = 0; $j < $x->t; $j++) {
                $z->c[$j] = $y->c[$j + $x->t];
            }
        }
        
        $y->n = $x->t - 1;
        
        for ($j = $x->n; $j >= $i + 1; $j--) {
            $x->c[$j+1] = $x->c[$j];            
        }
        
        $x->c[$i+1] = $z;
        
        for ($j = $x->n -1; $j >= $i; $j--) {
            $x->key[$j+1] = $x->key[$j];
        }
        
        $x->key[$i] = $y->key[$x->t - 1];
        $x->n = $x->n + 1;
        
        $y->root = $this->root;
        $z->root = $this->root;
        $x->root = $this->root;
    }
}

