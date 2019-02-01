<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 30. 1. 2019
 * Time: 16:08
 */

/**
 * Class Row
 *
 * @author Tomáš Babický tomas.babicky@websta.de
 */
class Row
{
    private $row;

    /**
     * Row constructor.
     */
    public function __construct($data)
    {
        $this->row = new stdClass();
        
        foreach ($data as $key => $value) {
            $this->row->{$key} = $value;
        }
    }

    public function get()
    {
        return $this->row;
    }

    /**
     * Row destructor.
     */
    public function __destruct()
    {
        $this->row = null;
    }
}
