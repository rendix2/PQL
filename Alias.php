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
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class Alias
{
    /**
     * @var string
     */
    public const string DELIMITER = '.';

    /**
     * @var Table|string $from
     */
    private Table|string $from;

    /**
     * @var string $to
     */
    private string $to;

    /**
     * Alias constructor.
     *
     * @param Table|string $from
     * @param string $to
     */
    public function __construct(Table|string $from, string $to)
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

    public function getFrom(): Table|string
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public static function hasAlias(string $string): bool
    {
        return strpos($string, self::DELIMITER) !== false;
    }
}
