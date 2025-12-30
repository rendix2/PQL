<?php

namespace pql;

/**
 * Class Operator
 *
 * @author  rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class Operator
{
    private const array ENABLED_OPERATORS = [
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
        self::EXISTS
    ];

    public const string EQUAL = '=';

    public const string LESS_THAN = '<';

    public const string GREATER_THAN = '>';

    public const string LESS_EQUAL_THAN = '<=';

    public const string GREATER_EQUAL_THAN = '>=';

    public const string NON_EQUAL = '!=';

    public const string LESS_AND_GREATER_THAN = '<>';

    public const string IN = 'in';

    public const string NOT_IN = 'not_in';

    public const string IS_NULL = 'is_null';

    public const string IS_NOT_NULL = 'is_not_null';

    public const string BETWEEN = 'between';

    const string BETWEEN_INCLUSIVE = 'between_in';

    const string REGULAR_EXPRESSION = 'regexp';

    const string EXISTS = 'exists';

    public static function isOperatorValid(string $operator): bool
    {
        return in_array($operator, self::ENABLED_OPERATORS,true);
    }
}
