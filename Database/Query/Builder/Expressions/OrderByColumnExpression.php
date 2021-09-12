<?php
/**
 *
 * Created by PhpStorm.
 * Filename: OrderByColumn.php
 * User: Tomáš Babický
 * Date: 30.08.2021
 * Time: 13:25
 */

namespace PQL\Query\Builder;


use Nette\NotImplementedException;
use PQL\Query\Builder\Expressions\Column;
use PQL\Query\Builder\Expressions\IExpression;

class OrderByColumnExpression implements IExpression
{

    private Column $column;

    private bool $asc;

    /**
     * OrderByColumn constructor.
     *
     * @param Column $column
     * @param bool   $asc
     */
    public function __construct(Column $column, bool $asc)
    {
        $this->column = $column;
        $this->asc = $asc;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return Column
     */
    public function getColumn(): Column
    {
        return $this->column;
    }

    /**
     * @return bool
     */
    public function isAsc(): bool
    {
        return $this->asc;
    }

    public function getSortingConst()
    {
        return $this->asc === true ? SORT_ASC : SORT_DESC;
    }

    public function evaluate()
    {
        throw new NotImplementedException();
    }
}