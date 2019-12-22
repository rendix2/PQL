<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 11. 12. 2019
 * Time: 16:52
 */

/**
 * Class OrderBy
 *
 */
class OrderBy
{
    /**
     * @var string $column
     */
    private $column;

    /***
     * @var bool $asc
     */
    private $asc;

    /**
     * OrderBy constructor.
     *
     * @param string $column
     * @param bool   $asc
     */
    public function __construct($column, $asc = true)
    {
        $this->column = $column;
        $this->asc    = $asc;
    }

    /**
     * OrderBy destructor.
     */
    public function __destruct()
    {
        $this->column = null;
        $this->asc    = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->column . ' ' . $this->getSortingWord();
    }

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return bool
     */
    public function getAsc()
    {
        return $this->asc;
    }

    /**
     * @return int
     */
    public function getSortingConst()
    {
        return $this->asc === true ? SORT_ASC : SORT_DESC;
    }

    /**
     * @return string
     */
    public function getSortingWord()
    {
        return $this->asc === true ? 'ASC' : 'DESC';
    }
}
