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

    public $keys;
    public $t;
    public $c;
    public $n;
    public $leaf;
    
    public function __construct($t, $leaf)
    {
        $this->t = $t;
        $this->leaf = $leaf;
        $this->n = 0;
        $this->c = [];
        $this->keys = [];
    }
    
    public function insertNonFull($k) 
    {
        
    }
    
    public function splitChild($i, Node $y)
    {
        
    }
    
    
}
