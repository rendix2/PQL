<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 11. 12. 2019
 * Time: 14:47
 */

/**
 * Class Alias
 *
 */
class Alias
{
    /**
     * @var mixed $from
     */
    private $from;

    /**
     * @var mixed $to
     */
    private $to;

    /**
     * Alias constructor.
     *
     * @param mixed $from
     * @param mixed $to
     */
    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Alias destructor.
     */
    public function __destruct()
    {
        $this->from = null;
        $this->to   = null;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }
}
