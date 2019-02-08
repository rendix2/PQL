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
     * @var BTree $root
     */
    public $root;
    
    public $t;
    
    /**
     * BTree constructor.
     */
    public function __construct($t)    
    {
        $this->t    = $t;
        $this->root = null;
    }

    /**
     * BTree destructor.
     */
    public function __destruct()
    {
    }
    
    public static function create(BTree $T)
    {
        $y = new Node(5, true);
        $y->n = 0;
        $T->root = $y;
        
        return $y;
    }
    

    public function insert(BTree $T, $k)
    {
        $r = $T->root;
        
        if ($r->n === 2 * $this->t - 1) {
            $s = new Node($this->t, false);
            $T->root = $s;
            $s->n = 0;
            $s->c[0] = $r;
            
            $s->splitChild($s, $k);
        } else {
            $r->splitChild($r, $k);
        }
    }

    public function delete()
    {

    }

    public function search($key)
    {

    }
}
