<?php

/**
 * Class Operator
 */
class Operator
{
    /**
     * @var array
     */
    const ENABLED_OPERATORS = [
        self::EQUAL,
        self::LESS_THAN,
        self::GREATER_THAN,
        self::LESS_EQUAL_THAN,
        self::GREATER_EQUAL_THAN,
        self::NON_EQUAL,
        self::LESS_AND_GREATER_THAN,
        self::IN,
        self::NOT_IN,
        self::IS_NULL,
        self::IS_NOT_NULL,
        self::BETWEEN,
        self::BETWEEN_INCLUSIVE,
        self::REGULAR_EXPRESSION,
    ];

    /**
     * @var string
     */
    const EQUAL = '=';

    /**
     * @var string
     */
    const LESS_THAN = '<';

    /**
     * @var string
     */
    const GREATER_THAN = '>';

    /**
     * @var string
     */
    const LESS_EQUAL_THAN = '<=';

    /**
     * @var string
     */
    const GREATER_EQUAL_THAN = '>=';

    /**
     * @var string
     */
    const NON_EQUAL = '!=';

    /**
     * @var string
     */
    const LESS_AND_GREATER_THAN = '<>';

    /**
     * @var string
     */
    const IN = 'in';

    /**
     * @var string
     */
    const NOT_IN = 'not_in';

    /**
     * @var string
     */
    const IS_NULL = 'is_null';

    /**
     * @var string
     */
    const IS_NOT_NULL = 'is_not_null';

    /**
     * @var string
     */
    const BETWEEN = 'between';

    /**
     * @var string
     */
    const BETWEEN_INCLUSIVE = 'between_in';

    /**
     * @var string
     */
    const REGULAR_EXPRESSION = 'regexp';

    /**
     * @param string $operator
     *
     * @return bool
     */
    public static function isOperatorValid($operator)
    {
        return in_array($operator, self::ENABLED_OPERATORS, true);
    }
}
