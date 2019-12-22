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
     * @var string
     */
    const DELIMITER = '.';

    /**
     * @var Table $from
     */
    private $from;

    /**
     * @var string $to
     */
    private $to;

    /**
     * Alias constructor.
     *
     * @param Table $from
     * @param string $to
     */
    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to   = $to;
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
     * @return Table
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }
}
