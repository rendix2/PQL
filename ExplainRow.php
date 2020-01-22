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
class ExplainRow implements IRow
{
    /**
     * @var string $table
     */
    private $table;

    /**
     * @var int|string $rows
     */
    private $rows;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $condition
     */
    private $condition;

    /**
     * @var string $algorithm
     */
    private $algorithm;

    /**
     * @var ExplainRow[] $sub
     */
    private $sub;

    /**
     * ExplainRow constructor.
     *
     * @param $table
     * @param $rows
     * @param $type
     * @param $condition
     * @param $algorithm
     * @param $sub
     */
    public function __construct($table, $rows, $type, $condition, $algorithm, $sub)
    {
        $this->table     = $table;
        $this->rows      = $rows;
        $this->type      = $type;
        $this->condition = $condition;
        $this->algorithm = $algorithm;
        $this->sub       = $sub;
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
        $this->sub       = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Name: ' . $this->table . ', Type: ' . $this->type . ', Rows count: ' . $this->rows;
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
    public function getSub()
    {
        return $this->sub;
    }

    /**
     * @return mixed
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }
}
