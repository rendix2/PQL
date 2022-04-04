<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IntArrayValue.php
 * User: Tomáš Babický
 * Date: 01.01.2022
 * Time: 4:25
 */

namespace PQL\Database\Query\Builder\Expressions;

use Nette\InvalidArgumentException;

/**
 * Class IntArrayValue
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
class IntArrayValue extends ArrayValue
{
    /**
     * @param array       $values
     * @param string|null $alias
     */
    public function __construct(array $values, ?string $alias = null)
    {
        foreach ($values as $value) {
            if (!is_int($value)) {
                throw new InvalidArgumentException('Not integer value.');
            }
        }

        parent::__construct($values, $alias);
    }
}
