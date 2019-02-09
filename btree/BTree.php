<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 7. 2. 2019
 * Time: 17:51
 */

namespace BTree;

/**
 * Class BTree
 *
 * @author Tomáš Babický tomas.babicky@websta.de
 */
class BTree
{
    /**
     *
     * @var Node $root
     */
    public $root;
    
    public $t;
    
    public $n;
    
    public $leaf;
    
    public $c;
    
    public $keys;
    
    public function __construct($t)
    {
        $this->root = $this;
        $this->t    = $t;
        $this->c = [];
        $this->n = 0;
        $this->leaf = true;
        //$this->keys = [];
    }
    
    public function traverse()
    {
        $this->root->traverse();
    }
    
    public function search($k)
    {
        // Find the first key greater than or equal to k
        $i = 1;
        
        /**
        while ($i <= $this->n  && $k > $this->keys[$i]) {
            $i++;
        }
        */
        
        
        //$i--;
        
        bdump($i, '$i');
            
            // If the found key is equal to k, return this node
        if ( $k === $this->keys[$i]) {
            bdump('OK');
            
            return $k;
        }
        
        
        if ($this->c[$i]->leaf === true ) {
            bdump('fal');
            return null;
        }
        
                
                // If key is not found here and this is a leaf node
                /**
                 * 
                 
        if ($this->leaf === true) {
            return NULL;
        }
        */
        
        if (!count($this->c[$i])) {
            bdump('FALSE');
            return false;
        }
        
        bdump($this, '$this');
        
        
                    
                    
                    // Go to the appropriate child
                    return $this->c[$i]->search($k);
    }
    
    public function insert($k)
    {        
        // If tree is empty
        if ($this->root == NULL)
        {
            // Allocate memory for root
            $root = new Node($this->t, true);
            $root->keys[0] = $k; // Insert key
            $root->n = 1; // Update number of keys in root
        }
        else // If tree is not empty
        {
            // If root is full, then tree grows in height
            if ($this->root->n == 2*$this->t-1)
            {
                //bdump('node is full '. $k);
                
                // Allocate memory for new root
                $s = new BTree($k);                
                                
                // Make old root as child of new root
                //$s->c[0] = $this->root;
                
                $this->root->c[1] = $s;
                
                // Split the old root and move 1 key to the new root
                $s->splitChild(0, $this->root);
                
                $this->root->leaf = false;
                
                // New root has two children now. Decide which of the
                // two children is going to have new key
                $i = 0;
                if ($s->keys[0] < $k) {
                    //bdump('left');
                    $i++;
                } else {
                    //bdump('right');
                }
                 
                    $s->c[$i]->insertNonFull($k);
                    
                    //bdump($s, '$s');
                    
                    // Change root
                    $this->root = $s;
            }
            else // If root is not full, call insertNonFull for root
            {
                //bdump('node is NOT full, calling insertNonFUll() ' . $k);
                
                //$this->root->keys = [];
                $this->root->insertNonFull($k);
            }
        } 
        
    }
    
    private function insertNonFull($k)
    {
        //bdump('inserting non full: '. $k);
        
        // Initialize index as index of rightmost element
        $i = $this->n-1;
        
        if ($i === -1) {
            $i = 0;
        }
        
        // If this is a leaf node
        if ($this->leaf == true)
        {
            //bdump('this->leaf === true');
            
            // The following loop does two things
            // a) Finds the location of new key to be inserted
            // b) Moves all greater keys to one place ahead

            
            if ($this->keys === null) {
                $this->keys = [];
            }
            
            
            //bdump($this->keys, '$this->keys');
            //bdump(isset($this->keys[$i]), 'isset($this->keys[$i])');
            

            
            
           // bdump($this->keys[$i], '$this->keys[$i]');
            //bdump($i, '$i');
            //bdump($this);
            
            //if (!isset($this->keys[$i])) {
                //bdump('return' );
                
                //return ;
            //}

            
            
            while ($i >= 0 &&  isset($this->keys[$i]) && $this->keys[$i] > $k)
            {
                //$this->keys[$i+1] = $this->keys[$i];
                $i--;
            }
            
            // Insert the new key at found location
            $this->keys[$i+1] = $k;
            $this->n          = $this->n + 1;
        }
        else // If this node is not leaf
        {
            // Find the child which is going to have the new key
            while ($i >= 0 && $this->keys[$i] > $k)
                $i--;
                
                // See if the found child is full
                if ($this->c[$i+1]->n == 2*$this->t-1)
                {
                    // If the child is full, then split it
                    $this->splitChild($i+1, $this->c[$i+1]);
                    
                    // After split, the middle key of C[i] goes up and
                    // C[i] is splitted into two. See which of the two
                    // is going to have the new key
                    if ($this->keys[$i+1] < $k)
                        $i++;
                }
               // bdump($this);
                
                $this->c[$i+1]->insertNonFull($k);
        } 
    }
    
    private function splitChild($i, BTree $y) 
    {
       // bdump('split');
        //bdump($y, '$y');
        
        $z = new BTree($y->t);
        $z->n = $this->t - 1;
        
        //$z->keys = $y->keys;
        
        
        // Copy the last (t-1) keys of y to z
        /*for ($j = 0; $j < $this->t - 1; $j++) {
            $z->keys[$j] = $y->keys[$j];
        }
        */
        
       
        $z->keys = $y->keys;
        //$y->keys = null;
        
        //bdump($z->keys, '$z->keyys');
        
        
        
        //bdump($z, '$z');
            
        
            // Copy the last t children of y to z
            if ($y->leaf === false)
            {
                //bdump('splitting: node is leaf');
                
                for ($j = 0; $j < $this->t; $j++)
                    $z->c[$j] = $y->c[$j+$this->t];
            }
           

            
            // Reduce the number of keys in y
            $y->n = $this->t - 1;
            
            // Since this node is going to have a new child,
            // create space of new child
            for ($j = $this->n; $j >= $i+1; $j--) {
                $this->c[$j+1] = $this->c[$j];
            }
            
            
            //bdump($this, '$this OK');
                
                // Link the new child to this node
                $this->c[$i+1] = $z;
                
              //  bdump($this, '$this');
                
                // A key of y will move to this node. Find location of
                // new key and move all greater keys one space ahead
                for ($j = $this->n-1; $j >= $i; $j--) {
                    $this->keys[$j+1] = $y->keys[$j];
                }
                
                //$y->keys = null;
                
                //bdump($this, '$this after foreach');
                //bdump($this->keys, '$this->keys');    
                
                
                    // Copy the middle key of y to this node
                    //$this->keys[$i] = $y->keys[$i];
                    
                    //bdump($this->keys, '$this->keys');
                    
                    //bdump($this, 'prirazeni');
                    
                    // Increment count of keys in this node
                    $this->n = $this->n + 1; 
    }
    
}
