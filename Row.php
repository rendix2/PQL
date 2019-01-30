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
    public function __construct()
    {
        $this->row = new stdClass();
    }

    /**
     * Row destructor.
     */
    public function __destruct()
    {
        $this->row = null;
    }
}
