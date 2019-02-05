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
 * @author rendix2
 */
class Row
{
    private $row;

    /**
     * Row constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->row = new stdClass();
        
        foreach ($data as $key => $value) {
            $this->row->{$key} = $value;
        }
    }

    /**
     * Row destructor.
     */
    public function __destruct()
    {
        $this->row = null;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->row->{$name});
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->row->{$name});
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->row->{$name} = $value;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->row->{$name};
    }

    /**
     * @return stdClass
     */
    public function get()
    {
        return $this->row;
    }
}
