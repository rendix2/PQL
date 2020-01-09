<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 11. 12. 2019
 * Time: 14:47
 */

namespace pql;

/**
 * Class Alias
 *
 * @author rendix2 <rendix2@seznam.cz>

 */
class Alias
{
    /**
     * @var string
     */
    const DELIMITER = '.';

    /**
     * @var Table|string $from
     */
    private $from;

    /**
     * @var string $to
     */
    private $to;

    /**
     * Alias constructor.
     *
     * @param Table|string $from
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

    /**
     * @param string $string
     *
     * @return bool
     */
    public static function hasAlias($string)
    {
        return strpos($string, self::DELIMITER) !== false;
    }
}
