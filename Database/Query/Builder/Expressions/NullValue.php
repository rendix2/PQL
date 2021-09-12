<?php
/**
 *
 * Created by PhpStorm.
 * Filename: NullValue.php
 * User: Tomáš Babický
 * Date: 30.08.2021
 * Time: 14:00
 */

namespace PQL\Query\Builder\Expressions;


class NullValue implements IValue
{

    public function evaluate()
    {
        return null;
    }

}