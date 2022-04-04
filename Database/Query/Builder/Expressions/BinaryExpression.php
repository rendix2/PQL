<?php
/**
 *
 * Created by PhpStorm.
 * Filename: BinaryExpression.php
 * User: Tomáš Babický
 * Date: 07.01.2022
 * Time: 1:30
 */

namespace PQL\Database\Query\Builder\Expressions;

/**
 * Interface BinaryExpression
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
interface BinaryExpression
{
    /**
     * @return IExpression
     */
    public function getLeft() : IExpression;

    /**
     * @return IExpression
     */
    public function getRight() : IExpression;
}