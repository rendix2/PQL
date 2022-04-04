<?php
/**
 *
 * Created by PhpStorm.
 * Filename: OrderByColumn.php
 * User: Tomáš Babický
 * Date: 30.08.2021
 * Time: 13:25
 */

namespace PQL\Database\Query\Builder;

use Nette\NotImplementedException;
use PQL\Database\Query\Builder\Expressions\IExpression;

/**
 * Class OrderByExpression
 *
 * @package PQL\Database\Query\Builder
 */
class OrderByExpression implements IExpression
{
    /**
     * @var IExpression $expression
     */
    private IExpression $expression;

    /**
     * @var bool $asc
     */
    private bool $asc;

    /**
     * OrderByColumn constructor.
     *
     * @param IExpression $expression
     * @param bool        $asc
     */
    public function __construct(IExpression $expression, bool $asc)
    {
        $this->expression = $expression;
        $this->asc = $asc;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }
    }

    /**
     * @return IExpression
     */
    public function getExpression() : IExpression
    {
        return $this->expression;
    }

    /**
     * @return bool
     */
    public function isAsc() : bool
    {
        return $this->asc;
    }

    public function getSortingConst() : int
    {
        return $this->asc === true ? SORT_ASC : SORT_DESC;
    }

    public function evaluate() : string
    {
        throw new NotImplementedException();
    }

    public function print(?int $level = null) : string
    {
        throw new NotImplementedException();
    }
}