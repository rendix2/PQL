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
    private $leaf;

    /**
     * Node constructor.
     *
     * @param $leaf
     */
    public function __construct($leaf)
    {
        $this->leaf = $leaf;
    }

    /**
     * Node destructor.
     */
    public function __destruct()
    {
        $this->leaf = null;
    }

    public static function create()
    {

    }
}
