<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 5. 2. 2019
 * Time: 13:14
 */

namespace pql\QueryExecutor;

/**
 * Class Functions
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql\QueryExecute
 */
class Functions
{
    /**
     * @var array $table
     */
    private $table;

    /**
     * Functions constructor.
     *
     * @param array $table
     */
    public function __construct(array $table)
    {
        $this->table = $table;
    }

    /**
     * Functions destructor.
     */
    public function __destruct()
    {
        $this->table = null;
    }

    /**
     * @param string $column
     *
     * @return int
     */
    public function count($column)
    {
        return count(array_column($this->table, $column));
    }

    /**
     * @param string $column
     *
     * @return float|int
     */
    public function sum($column)
    {
        return array_sum(array_column($this->table, $column));
    }

    /**
     * @param string $column
     *
     * @return float|int
     */
    public function avg($column)
    {
        $values = array_column($this->table, $column);

        return array_sum($values) / $this->count($column);
    }

    /**
     * @param string $column
     *
     * @return mixed
     */
    public function min($column)
    {
        return min(array_column($this->table, $column));
    }

    /**
     * @param string $column
     *
     * @return mixed
     */
    public function max($column)
    {
        return max(array_column($this->table, $column));
    }

    /**
     * @param string $column
     *
     * @return float|int
     */
    public function median($column)
    {
        $values = array_column($this->table, $column);
        $count  = count($values);

        sort($values);

        if ($count % 2) {
            return $values[$count / 2];
        } else {
            return ($values[$count / 2] + $values[($count / 2) - 1]) / 2;
        }
    }
}
