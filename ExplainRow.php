<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 16. 1. 2020
 * Time: 16:44
 */

namespace pql;

/**
 * Class ExplainRow
 *
 * @author rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class ExplainRow
{
    private $table;

    private $rows;

    private $type;

    private $condition;

    private $algorithm;

    /**
     * ExplainRow constructor.
     *
     * @param $table
     * @param $rows
     * @param $type
     * @param $condition
     * @param $algorithm
     */
    public function __construct($table, $rows, $type, $condition, $algorithm)
    {
        $this->table     = $table;
        $this->rows      = $rows;
        $this->type      = $type;
        $this->condition = $condition;
        $this->algorithm = $algorithm;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return mixed
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @return mixed
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * ExplainRow destructor.
     */
    public function __destruct()
    {
        $this->table     = null;
        $this->rows      = null;
        $this->type      = null;
        $this->condition = null;
        $this->algorithm = null;
    }
}
