<?php
/**
 *
 * Created by PhpStorm.
 * Filename: StandardColorizer.php
 * User: Tomáš Babický
 * Date: 17.09.2021
 * Time: 23:18
 */

namespace PQL\Database\Query\Printer\Colorizer;

/**
 * Class StandardColorizer
 *
 * @package PQL\Database\Query\Printer\Colorizer
 */
class StandardColorizer implements IColorizer
{
    private string $clauseSpan;
    private string $closeSpan;
    private string $columnSpan;
    private string $operatorSpan;
    private string $tableSpan;

    public function __construct()
    {
        $this->tableSpan = '<span style="color: orangered">';
        $this->columnSpan = '<span style="color: darkmagenta">';
        $this->clauseSpan = '<span style="color: blue">';
        $this->operatorSpan = '<span style="color:darkgray">';
        $this->closeSpan = '</span>';
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return string
     */
    public function getClauseSpan() : string
    {
        return $this->clauseSpan;
    }

    /**
     * @return string
     */
    public function getCloseSpan() : string
    {
        return $this->closeSpan;
    }

    /**
     * @return string
     */
    public function getColumnSpan() : string
    {
        return $this->columnSpan;
    }

    /**
     * @return string
     */
    public function getOperatorSpan() : string
    {
        return $this->operatorSpan;
    }

    /**
     * @return string
     */
    public function getTableSpan() : string
    {
        return $this->tableSpan;
    }
}