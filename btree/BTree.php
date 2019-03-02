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
    
    public $capacity;
    
    public $values;
    
    public $valuesCount;
    
    public $nodes;
    
    public $nodesCount;
    
    public $parent;
    
    public $leaf;
    
    public $root;
    
    public function __construct()
    {
        $this->capacity = 5;
        $this->valuesCount = 0;
        $this->nodes = [];
        $this->nodesCount = 0;
        $this->parent = null;
        $this->leaf = true;
        $this->root = null;
    }
    
    public function insert($value)
    {        
        if ($this->valuesCount < $this->capacity - 1) {
            $this->insertNonFull($this, $value);
        } else {
            $this->split($this);
            
            $i = 0;
            
            if ($this->nodes[$i]->values[0] < $value) {
                $i++;   
            }            
            
            $this->insertNonFull($this->nodes[$i], $value);
            
        }
       
        
        return $this;
    }
    
    public function insertNonFull(BTree $node, $value) 
    {
        
        
        if ($node->leaf) {
            //bdump($value, 'leaf');
            
            $node->values[] = $value;            
            sort($this->values);            
            $node->valuesCount++;
        } else {
            //bdump($value, '!leaf');
            //bdump($node, '$node');
            
            //bdump($node->valuesCount, '$node->valuesCount');
            //bdump($node->capacity, '$node->capacity');
            //bdump($node, '$node');
            
            if ($node->nodes[$this->nodesCount + 1]->valuesCount === $node->capacity - 1) {
                //bdump('We need to plit');
                
                $this->split($node->nodes[$this->nodesCount + 1]);
            }
            
            $this->insertNonFull($node->nodes[$this->nodesCount + 1], $value);
        }
    }
    
    public function split(BTree $node)
    {
        $left = new BTree();        
        $right = new BTree();
        
        if ($node->leaf) {
            $node->leaf = false;
            $left->parent = $node;
            $right->parent = $node;
        } 
        
        $node->nodes[] = $left;
        $node->nodes[] = $right;
                
        $left->nodesCount = $node->nodesCount;
                
        //$left->values[] = $value;

        
        $chunk = array_chunk($node->values, $node->capacity / 2);
        
        
        
        $left->values = $chunk[0];
        $left->valuesCount = count($chunk[0]);
        $right->values = $chunk[1];
        
        $middle = $node->values[$node->capacity/2];
        $node->values = [];
        $node->values[] = $middle;
        $node->valuesCount = count($node->values);
        
        unset($right->values[0]);
        $rightValues = array_values($right->values);
        $right->values = $rightValues;
        $right->valuesCount = count($rightValues);
        
        
        //if ()
        
        
        bdump($chunk);
        
        /*
        
        for ($i = 0; $i < $node->capacity / 2 - 1; $i++) {
            $left->values[] = $node->values[$i];
            $left->valuesCount++;
            $node->valuesCount--;
            unset($node->values[$i]);
        }
        
        if ($this->capacity % 2 === 0) {
            $middleValue = $node->values[$this->capacity / 2 - 1]; 
            
            
            for ($i = $node->capacity / 2; $i < $this->capacity; $i++) {
                $right->values[] = $node->values[$i];
                $right->valuesCount++;
                $node->valuesCount--;
                unset($node->values[$i]);
            }
            //$node->values[] = $middleValue;
            
        } else {
            $middleValue = $node->values[$this->capacity / 2];
            
            bdump($middleValue, '$middle');
            bdump($node->values);
            
            
            for ($i = $node->capacity / 2 + 1; $i < $this->capacity -1 ; $i++) {
                
                
                
                $right->values[] = $node->values[$i];
                $right->valuesCount++;
                $node->valuesCount--;
                unset($node->values[$i]);
            }
            
            bdump($node->values);
            
            //$node->values[] = $middleValue;
        }
        
        */
    }
}
